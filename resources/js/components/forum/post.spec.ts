import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import VuePost from './post.vue';
import VueTimeago from '../../plugins/timeago';
import { post as fake, topic } from '../../faker';
import store from '../../store';

Vue.use(VueTimeago);

describe('Deleted post', () => {
  store.commit('topics/init', [ topic ]);

  const wrapper = shallowMount(VuePost, {propsData: {post: fake({deleted_at: new Date()})}, store });

  it('should not allow to vote', () => {
    expect(wrapper.find('.fa-thumbs-up').exists()).toBeFalsy();
  });

  it('should not allow to accept', () => {
    expect(wrapper.find('.fa-check').exists()).toBeFalsy();
  });
});

describe('Regular post', () => {
  it('should not allow to accept', () => {
    store.commit('topics/init', [ topic ]);

    const wrapper = shallowMount(VuePost, {propsData: {post: fake()}, store });

    expect(wrapper.find('.fa-check').exists()).toBeFalsy();
  });

  it('should allow to accept', () => {
    store.commit('topics/init', [ topic ]);

    const wrapper = shallowMount(VuePost, {propsData: {post: fake({permissions: {accept: true}})}, store});

    expect(wrapper.find('.fa-check').exists()).toBeTruthy();
  });

  it('should show ip', () => {
    store.commit('topics/init', [ topic ]);

    const ip = '192.168.0.1';
    const wrapper = shallowMount(VuePost, {propsData: {post: fake(), ip}, store});

    expect(wrapper.html()).toContain(ip);
  });
});

