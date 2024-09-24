import {createLocalVue, mount, type Wrapper} from "@vue/test-utils";
import Vue, {VueConstructor} from "vue";
import VueNotifications from "vue-notification";

export function render(component: any, props: object = {}): Component {
  const localVue = vueWithNotifications();

  return new Component(
    mount(component, {propsData: props, localVue}),
    mount({template: '<vue-notifications/>'}, {localVue}),
  );
}

function vueWithNotifications(): VueConstructor<Vue> {
  Vue.use(VueNotifications, {componentName: 'vue-notifications'});

  const vue: VueConstructor<Vue> = createLocalVue();
  vue.use(VueNotifications, {componentName: 'vue-notifications'});
  return vue;
}

export class Component {
  public readonly notifications: Notifications;
  public readonly vm: Vue;

  constructor(private wrapper: Wrapper<Vue>, notifications: Wrapper<Vue>) {
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
    return this.eventsEmitted(eventName)[0][0];
  }

  private eventsEmitted(eventName: string): any[][] {
    const emitted = this.wrapper.emitted();
    if (emitted[eventName] === undefined) {
      throw new Error('Failed to assert event was emitted: ' + eventName);
    }
    return emitted[eventName]!;
  }

  classes(): string[] {
    return this.elementClasses(this.wrapper);
  }

  classesOf(cssSelector: string): string[] {
    return this.elementClasses(this.wrapper.find(cssSelector));
  }

  private elementClasses(wrapper: Wrapper<Vue>): string[] {
    return [...wrapper.element.classList.values()];
  }

  exists(cssSelector: string): boolean {
    return this.wrapper.find(cssSelector).exists();
  }

  passedTo(child: object, property: string): string {
    return this.child(child).props(property);
  }

  private child(child: object): Wrapper<Vue> {
    return this.wrapper.findComponent(child);
  }

  async emitFrom(child: object, eventName: string, args: any[] = []): Promise<void> {
    await this.emit(this.child(child).vm, eventName, args);
  }

  private async emit(child: Vue, eventName: string, args: any[]): Promise<void> {
    child.$emit(eventName, ...args);
    await child.$nextTick();
  }
}

class Notifications {
  constructor(private notifications: Wrapper<Vue>) {
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
