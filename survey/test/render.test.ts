import {describe, test} from '@jest/globals';
import {nextTick} from 'vue';
import {notify} from "../../resources/js/toast";
import {VueInstance} from "../src/vue";
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

  test('find attribute by selector', async () => {
    const component = render({template: '<a href="foo"/>'});
    assertEquals(component.attributeOf('a', 'href'), 'foo');
  });

  test('find classes by selector', async () => {
    const component = render({template: '<a class="foo bar"/>'});
    assertEquals(component.classesOf('a'), ['foo', 'bar']);
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
    test('no event', () => {
      const empty = render({template: '<div/>'});
      assertFalse(empty.emitted('input'));
    });

    test('emitted', () => {
      const emitter = render({
        template: '<div/>',
        created(this: VueInstance): void {
          this.$emit('input');
        },
      });
      assertTrue(emitter.emitted('input'));
    });
  });

  test('inspect emitted value', () => {
    const emitter = render({
      template: '<div/>',
      created(this: VueInstance): void {
        this.$emit('input', 'foo');
      },
    });
    assertEquals(emitter.emittedValue('input'), 'foo');
  });

  test('container css classes', () => {
    const classer = render({template: '<div class="foo bar"/>'});
    assertEquals(classer.classes(), ['foo', 'bar']);
  });

  describe('exists()', () => {
    test('element exists', () => {
      const classer = render({template: '<div><span id="foo"/></div>'});
      assertTrue(classer.exists('#foo'));
    });

    test('element is missing', () => {
      const classer = render({template: '<div/>'});
      assertFalse(classer.exists('#foo'));
    });
  });

  describe('whitebox', () => {
    test('passes properties to children', () => {
      const inner = {template: '<div>inner</div>', props: ['foo']};
      const parent = render({components: {inner}, template: '<inner foo="bar"/>'});
      assertEquals(parent.passedTo(inner, 'foo'), 'bar');
    });

    test('emit events from children', () => {
      const inner = {template: '<div/>'};
      let received = false;
      const parent = render({
        components: {inner},
        template: '<inner @foo="received"/>',
        methods: {
          received(): void {
            received = true;
          },
        },
      });
      parent.emitFrom(inner, 'foo');
      assertTrue(received);
    });

    test('wait until emit is called', async () => {
      const inner = {template: '<div/>'};
      const parent = render({
        components: {inner},
        template: `
          <div>
            <inner @foo="value='after';"/>
            {{ value }}
          </div>`,
        data() {
          return {value: 'before'};
        },
      });
      await parent.emitFrom(inner, 'foo');
      assertEquals(parent.text(), 'after');
    });

    test('emit properties from children', () => {
      const inner = {template: '<div/>'};
      let received = null;
      const parent = render({
        components: {inner},
        template: '<inner @foo="received"/>',
        methods: {
          received(argument): void {
            received = argument;
          },
        },
      });
      parent.emitFrom(inner, 'foo', ['bar']);
      assertEquals(received, 'bar');
    });
  });

  test('read notification title', async () => {
    const component = render({template: '<div/>'});
    notify({title: 'bar'});
    await nextTick();
    assertEquals(component.notifications.title(), 'bar');
  });

  test('receive icon', async () => {
    const icon = {
      inject: ['icons'],
      template: '<span v-text="icons.foo"/>',
    };
    const component = render(icon, {}, {foo: 'bar'});
    assertEquals(component.text(), 'bar');
  });

  test('passes slot', () => {
    const component = {template: '<div><slot/></div>'};
    const el = render(component, {}, {}, {default: 'foo'});
    assertEquals(el.textBy('div'), 'foo');
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
