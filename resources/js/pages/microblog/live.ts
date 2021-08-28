import { Subscriber, MicroblogCommentSaved, MicroblogSaved, MicroblogVoted } from "@/libs/live";

export default {
  methods: {
    liveNotifications() {
      const subscriber = new Subscriber(`microblog`);

      subscriber.subscribe('MicroblogSaved', new MicroblogSaved())
      subscriber.subscribe('MicroblogSaved', new MicroblogCommentSaved())
      subscriber.subscribe('MicroblogVoted', new MicroblogVoted())
    }
  }
}
