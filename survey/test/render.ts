import {mount} from "@vue/test-utils";

export function render(component: any, props: object = {}): Component {
  return new Component(mount(component, {propsData: props}));
}

export class Component {
  constructor(private wrapper: any) {
  }

  text(): string {
    return this.wrapper.text();
  }

  async click(cssSelector: string): Promise<void> {
    await this.wrapper.find(cssSelector).trigger('click');
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
}
