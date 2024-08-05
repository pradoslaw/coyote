import {describe, test} from '@jest/globals';
import {assertMatch} from "./assert";
import {render} from "./render";

describe('render', () => {
  test('accept vue property', () => {
    const Foo = {props: ['property'], template: '<p>{{ property }}</p>'};
    const component = render(Foo, {property: 'Hello world'});
    assertMatch(component.text(), /Hello world/);
  });

  test('renders nested component', () => {
    const inner = {template: '<div>inner</div>'};
    const component = render({components: {inner}, template: '<inner/>'});
    assertMatch(component.text(), /inner/);
  });

  test('trigger click event', async () => {
    const component = render(counter);
    assertMatch(component.text(), /count: 0/);
    await component.click('button');
    assertMatch(component.text(), /count: 1/);
  });
});

const counter = {
  data(): Members {
    return {
      count: 0,
    };
  },
  template: `
    <div>
      <p>count: {{ count }}</p>
      <button @click="inc"></button>
    </div>`,
  methods: {
    inc(this: Members): void {
      this.count++;
    },
  },
};

interface Members {
  count: number;
}
