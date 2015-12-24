<?php

namespace Coyote\Http\Requests;

use Coyote\Http\Requests\Request;

class PostRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $forum = $this->route('forum');
        return !(auth()->guest() && !$forum->enable_anonymous) || !$forum->is_locked;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $forum = $this->route('forum');
        $topic = $this->route('topic');
        $post = $this->route('post');

        // post requires text
        $rules = ['text' => 'required'];

        // if I create new topic as anonymous user (or edit anonymous' post as a moderator)...
        if ((isset($post->id) && is_null($post->user_id)) || auth()->guest()) {
            $rules = $rules + ['user_name' => 'required|string|max:20|unique:users,name'];
        }

        // if I create new topic or edit first post ...
        if ((isset($post->id) && $post->id === $topic->first_post_id) || is_null($topic)) {
            $rules = array_merge($rules, ['subject' => 'required|min:3|max:200', 'tag' => 'array']);

            $canSticky = $this->user() ? $this->user()->can('sticky', $forum) : false;
            if ($canSticky) {
                $rules['is_sticky'] = 'bool';
            }

            if ($forum->required_tag) {
                $rules['tag'] .= '|required';
            }

            if (is_array($this->request->get('tag'))) {
                foreach ($this->request->get('tag') as $key => $val) {
                    $rules['tag.' . $key] = 'required|max:25|tag|tag_creation:2';
                }
            }
        }

        return $rules;
    }
}
