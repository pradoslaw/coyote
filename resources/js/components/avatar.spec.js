import { mount } from '@vue/test-utils';
import VueAvatar from './avatar.vue';

describe('Basic avatar', () => {
  const wrapper = mount(VueAvatar, {propsData: {name: 'Foo'}});

  it('is a Vue instance', () => {
    expect(wrapper.exists()).toBeTruthy();
  });

  it('renders the correct markup', () => {
    expect(wrapper.html()).toContain('<img src="/img/avatar.png" alt="Foo" class="mw-100">');
  });
});
