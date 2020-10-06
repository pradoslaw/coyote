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

  it('should not allow to delete', () => {
    expect(wrapper.html()).not.toContain('Usuń');
    expect(wrapper.html()).not.toContain('Przywróć');
  });

  it('should not allow to report', () => {
    expect(wrapper.find('Raportuj').exists()).toBeFalsy();
  });
});

describe('Author post', () => {
  store.commit('topics/init', [ topic ]);

  const post = fake({permissions: {accept: true, update: true, delete: true, write: true}});
  const wrapper = shallowMount(VuePost, {propsData: { post }, store});

  it('should allow to accept', () => {
    expect(wrapper.find('.fa-check').exists()).toBeTruthy();
  });

  it('should allow to reply', () => {
    expect(wrapper.html()).toContain('Odpowiedz');
  });

  it('should allow to delete', () => {
    expect(wrapper.html()).toContain('Usuń');
  });

  it('should show ip', () => {
    store.commit('topics/init', [ topic ]);

    const ip = '192.168.0.1';
    const wrapper = shallowMount(VuePost, {propsData: {post: fake(), ip}, store});

    expect(wrapper.html()).toContain(ip);
  });
});

