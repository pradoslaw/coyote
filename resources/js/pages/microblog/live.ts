import { Microblog } from "../../types/models";
import store from "../../store";
import { default as ws } from "../../libs/realtime";
import Prism from "prismjs";


interface Observer {
  update(microblog: Microblog): void;
}

class LiveNotification {
  private observers: Observer[] = [];

  attach(observer: Observer) {
    this.observers.push(observer);
  }

  notify(microblog: Microblog) {
    for (const observer of this.observers) {
      observer.update(microblog);
    }
  }
}

class UpdateMicroblog implements Observer {
  update(microblog: Microblog) {
    const item = store.state.microblogs.data[microblog.id!];

    if (!item || item.is_editing) {
      return; // do not add new entries live (yet)
    }

    store.commit('microblogs/update', microblog);
  }
}

class UpdateComment implements Observer {
  update(comment: Microblog) {
    if (!comment.parent_id) {
      return;
    }

    const parent = store.state.microblogs.data[comment.parent_id];

    if (!parent || parent.comments[comment.id!]?.is_editing) {
      return;
    }

    store.commit(`microblogs/${comment.id! in parent.comments ? 'updateComment' : 'addComment'}`, { parent, comment });
  }
}

export default {
  methods: {
    liveNotifications() {
      const notification = new LiveNotification();

      notification.attach(new UpdateMicroblog());
      notification.attach(new UpdateComment());

      ws.subscribe('microblog').on('MicroblogSaved', (microblog: Microblog) => {
        if (store.getters['user/isBlocked'](microblog.user!.id)) {
          return;
        }

        // highlight not read text
        microblog.is_read = false;

        notification.notify(microblog);

        // highlight once again after saving
        // @ts-ignore
        this.$nextTick(() => Prism.highlightAll());
      });
    }
  }
}
