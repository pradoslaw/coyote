import {mount, type Wrapper} from "@vue/test-utils";
import Vue from "vue";

export function render(component: any, props: object = {}): Component {
  return new Component(mount(component, {propsData: props}));
}

export class Component {
  constructor(private wrapper: Wrapper<Vue>) {
  }

  text(): string {
    return this.wrapper.text();
  }

  textBy(cssSelector: string): string {
    return this.wrapper.find(cssSelector).text();
  }

  innerHtml(cssSelector: string): string {
    return this.wrapper.find(cssSelector).element.innerHTML;
  }

  inputChecked(cssSelector: string): boolean {
    const element = this.wrapper.find(cssSelector).element as HTMLInputElement;
    return element.checked;
  }

  async click(cssSelector: string): Promise<void> {
    await this.wrapper.find(cssSelector).trigger('click');
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
    if (typeof emitted[eventName] === 'undefined') {
      throw new Error('Failed to assert event was emitted: ' + eventName);
    }
    return emitted[eventName];
  }

  classes(): string[] {
    return [...this.wrapper.element.classList.values()];
  }
}
