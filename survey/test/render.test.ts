import {describe, test} from '@jest/globals';
import {assertEquals, assertFalse, assertMatch, assertTrue} from "./assert";
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

  test('find text by selector', async () => {
    const component = render({template: '<div>foo<p>bar</p></div>'});
    assertEquals(component.textBy('div p'), 'bar');
  });

  test('find html by selector', async () => {
    const component = render({template: '<div>foo<p>bar</p></div>'});
    assertEquals(component.innerHtml('div'), 'foo<p>bar</p>');
  });

  describe('checkbox attribute', () => {
    test('unchecked', async () => {
      const component = render({template: '<input type="checkbox"/>'});
      assertFalse(component.inputChecked('input'));
    });

    test('checked', async () => {
      const component = render({template: '<input type="checkbox" checked="checked"/>'});
      assertTrue(component.inputChecked('input'));
    });
  });

  describe('inspect emitted vue event', () => {
    test('no event', async () => {
      const empty = render({template: '<div/>'});
      assertFalse(empty.emitted('input'));
    });

    test('emitted', async () => {
      const emitter = render({
        template: '<div/>',
        created(this: Vue): void {
          this.$emit('input');
        },
      });
      assertTrue(emitter.emitted('input'));
    });
  });

  test('inspect emitted value', async () => {
    const emitter = render({
      template: '<div/>',
      created(this: Vue): void {
        this.$emit('input', 'foo');
      },
    });
    assertEquals(emitter.emittedValue('input'), 'foo');
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
