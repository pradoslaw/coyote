import { mount } from '@vue/test-utils';
import VueInbox from './inbox.vue';
import store from '../../store';
import {default as ws} from '../../libs/realtime.js';
import axios from 'axios';


jest.mock('../../libs/realtime.js', () => ({
  on: jest.fn()
}));

jest.mock('axios');

describe('Basic inbox', () => {
  store.commit('inbox/init', 1);

  const wrapper = mount(VueInbox);

  it('is a Vue instance', () => {
    expect(wrapper.exists()).toBeTruthy();
  });

  it('renders the correct messages number', () => {
    expect(wrapper.html()).toContain('<span class="badge">1</span>');
  });

  it('show messages', async () => {
    const resp = {
      data: {
        pm: [
          {
            created_at: "2020-10-13T18:46:59+02:00",
            excerpt: "Lorem ipsum",
            folder: 2,
            id: 472169,
            read_at: null,
            text: "<p>Lorem ipsum</p>",
            text_id: null,
            url: "/User/Pm/Show/472169",
            user: {
              deleted_at: null,
              id: 546,
              is_blocked: false,
              is_online: false,
              name: "Bald",
              photo: null
            }
          }
        ]
      }
    };

    axios.get.mockResolvedValue(resp);

    wrapper.find('.nav-link').trigger('click');

    await wrapper.vm.$nextTick();

    expect(wrapper.html()).toContain('Lorem ipsum');
  });
});
