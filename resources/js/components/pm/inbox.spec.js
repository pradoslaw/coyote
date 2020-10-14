import {mount, shallowMount} from '@vue/test-utils';
import Vue from 'vue';
import VueTimeago from '../../plugins/timeago';
import VueInbox from './inbox.vue';
import store from '../../store';
import {default as ws} from '../../libs/realtime.js';

// localVue.use(VueTimeago);

jest.mock('../../libs/realtime.js', () => ({
  on: jest.fn()
}));


describe('Basic inbox', () => {
  store.commit('inbox/init', 1);

  beforeAll(() => {
    Object.defineProperty(global, 'document', {});
  });


  const wrapper = mount(VueInbox);

  it('is a Vue instance', () => {
    expect(wrapper.exists()).toBeTruthy();
  });

  it('renders the correct messages number', () => {
    expect(wrapper.html()).toContain('<span class="badge">1</span>');
  });

  it('starts animation', () => {
    wrapper.vm.startAnimation({ name: 'adam '});

    expect(document.title).toContain('Masz wiadomość');
    // expect(wrapper.html()).toContain('<span class="badge">1</span>');
  });
});
