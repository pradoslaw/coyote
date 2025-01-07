import VueNotifications from "@kyvg/vue3-notification";
import {DOMWrapper, mount, VueWrapper} from "@vue/test-utils";
import {GlobalMountOptions} from "@vue/test-utils/dist/types";
import {ComponentPublicInstance, nextTick} from "vue";
import {VueInstance} from "../src/vue";

interface IconSet {
  [keyof: string]: string;
}

interface Slots {
  [slotName: string]: string;
}

interface Computed {
  [computedName: string]: () => any;
}

export function render(
  component: any,
  props: Record<string, unknown> = {},
  icons: IconSet = {},
  slots: Slots = {},
  computed: Computed = {},
): Component {
  const global = vueWithNotifications({icons});
  return new Component(
    mount(component, {
      props, global, slots, computed: {...component.computed, ...computed},
    }),
    mount({template: '<vue-library-notifications :dangerously-set-inner-html="true"/>'}, {global}),
  );
}

function vueWithNotifications(provide: object): GlobalMountOptions {
  return {
    plugins: [
      [VueNotifications, {componentName: 'vue-library-notifications'}],
    ],
    provide,
  };
}

export class Component {
  public readonly notifications: Notifications;
  public readonly vm: VueInstance;

  constructor(private wrapper: VueWrapper<ComponentPublicInstance>, notifications: VueWrapper<any>) {
    this.notifications = new Notifications(notifications);
    this.vm = wrapper.vm;
  }

  text(): string {
    return this.wrapper.text();
  }

  async click(cssSelector: string): Promise<void> {
    await this.wrapper.find(cssSelector).trigger('click');
  }

  textBy(cssSelector: string): string {
    return this.wrapper.find(cssSelector).text();
  }

  attributeOf(cssSelector: string, attribute: string): string {
    return this.wrapper.find(cssSelector).attributes()[attribute];
  }

  innerHtml(cssSelector: string): string {
    return this.wrapper.find(cssSelector).element.innerHTML;
  }

  inputChecked(cssSelector: string): boolean {
    const element = this.wrapper.find(cssSelector).element as HTMLInputElement;
    return element.checked;
  }

  async inputToggle(cssSelector: string): Promise<void> {
    const wrapper = this.wrapper.find(cssSelector);
    const element = wrapper.element as HTMLInputElement;
    element.checked = !element.checked; // simulate browser changing checked
    await wrapper.trigger('change');
  }

  emitted(eventName: string): boolean {
    const emitted = this.wrapper.emitted();
    return typeof emitted[eventName] !== 'undefined';
  }

  emittedValue(eventName: string): any {
    return this.eventsEmitted<any>(eventName);
  }

  private eventsEmitted<T>(eventName: string): T {
    const emitted: Record<string, T[][]> = this.wrapper.emitted();
    if (emitted[eventName] === undefined) {
      throw new Error('Failed to assert event was emitted: ' + eventName);
    }
    return emitted[eventName][0][0]!;
  }

  classes(): string[] {
    return this.elementClasses(this.wrapper);
  }

  classesOf(cssSelector: string): string[] {
    return this.elementClasses(this.wrapper.find(cssSelector));
  }

  private elementClasses(wrapper: DOMWrapper<Element> | VueWrapper): string[] {
    return [...wrapper.element.classList.values()];
  }

  exists(cssSelector: string): boolean {
    return this.wrapper.find(cssSelector).exists();
  }

  passedTo(child: object, property: string): string {
    return this.child(child).props(property);
  }

  private child(child: object): VueWrapper<any> {
    return this.wrapper.findComponent(child);
  }

  async emitFrom(child: object, eventName: string, args: any[] = []): Promise<void> {
    await this.emit(this.child(child).vm, eventName, args);
  }

  private async emit<T>(child: ComponentPublicInstance, eventName: string, args: any[]): Promise<void> {
    child.$emit(eventName, ...args);
    await this.waitForRefresh();
  }

  public async waitForRefresh(): Promise<void> {
    await nextTick();
  }
}

class Notifications {
  constructor(private notifications: VueWrapper<ComponentPublicInstance>) {
  }

  title(): string {
    return this.notifications.find('.vue-notification').find('.notification-title').text();
  }

  content(): string {
    return this.notifications.find('.vue-notification').find('.notification-content').element.innerHTML;
  }

  success(): boolean {
    return this.notifications.find('.vue-notification').find('.success').exists();
  }

  count(): number {
    return this.notifications.findAll('.vue-notification').length;
  }
}
