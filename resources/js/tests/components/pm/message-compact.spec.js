import { mount } from '@vue/test-utils';
import VueMessageCompact from '../../../components/pm/message-compact.vue';

const message = {
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
};

it('renders message', () => {
  const wrapper = mount(VueMessageCompact, {propsData: { message }});

  expect(wrapper.html()).toContain('<h4>Bald</h4>');
  expect(wrapper.html()).toContain("Lorem ipsum");
});
