import {mount} from "@vue/test-utils";

export function render(component: any, props: object = {}): Component {
  return new Component(mount(component, {propsData: props}));
}

class Component {
  constructor(private wrapper: any) {
  }

  text(): string {
    return this.wrapper.text();
  }

  async click(cssSelector: string): Promise<void> {
    await this.wrapper.find(cssSelector).trigger('click');
  }
}
