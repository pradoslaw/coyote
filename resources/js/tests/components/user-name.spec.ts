import { mount } from '@vue/test-utils';
import VueUserName from '../../components/user-name.vue';
import {User} from "../../types/models";

describe('Render component with regular user', () => {
  const user: User = {is_blocked: false, photo: "", id: 1, name: 'Foo'};
  const wrapper = mount(VueUserName, {propsData: { user }});

  it('is a Vue instance', () => {
    expect(wrapper.exists()).toBeTruthy();
  });

  it('renders the correct markup', () => {
    expect(wrapper.html()).toContain('<a class="" href="/Profile/1" data-user-id="1">Foo</a>');
  });
});

describe('Render component with blocked user', () => {
  const user: User = {is_blocked: true, photo: "", id: 1, name: 'Foo'};
  const wrapper = mount(VueUserName, {propsData: { user }});

  it('renders the correct markup', () => {
    expect(wrapper.html()).toContain('<a class="" style="text-decoration: line-through;" href="/Profile/1" data-user-id="1">Foo</a>');
  });
});

describe('Render component with removed user', () => {
  const user: User = {is_blocked: false, photo: "", id: 1, name: 'Foo', deleted_at: new Date()};
  const wrapper = mount(VueUserName, {propsData: { user }});

  it('renders the correct markup', () => {
    expect(wrapper.html()).toContain('<del class="" data-user-id="1">Foo</del>');
  });
});

