import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import VuePost from './post.vue';
import VueTimeago from '../../plugins/timeago';
import { post as fake } from '../../faker';

Vue.use(VueTimeago);

describe('Deleted post', () => {
  const wrapper = shallowMount(VuePost, {propsData: {post: fake({deleted_at: new Date()})}});

  it('should not allow to vote', () => {
    expect(wrapper.find('.fa-thumbs-up').exists()).toBeFalsy();
  });

  it('should not allow to accept', () => {
    expect(wrapper.find('.fa-check').exists()).toBeFalsy();
  });
});

describe('Regular post', () => {
  it('should not allow to accept', () => {
    const wrapper = shallowMount(VuePost, {propsData: {post: fake(), isAcceptAllowed: false}});

    expect(wrapper.find('.fa-check').exists()).toBeFalsy();
  });

  it('should allow to accept', () => {
    const wrapper = shallowMount(VuePost, {propsData: {post: fake(), isAcceptAllowed: true}});

    expect(wrapper.find('.fa-check').exists()).toBeTruthy();
  });
});

