import {Post, TreePost} from "../types/models";
import {TreeList} from "./treeList";

export type PostOrdering =
  'orderByCreationDateNewest' |
  'orderByCreationDateOldest' |
  'orderByMostLikes';

export function postsOrdered(posts: Post[], ordering: PostOrdering): TreePost[] {
  const tree = new TreeList<Post>(postOrdering(ordering));
  for (const post of posts) {
    if (!post.parentPostId) {
      tree.add(post.id, post);
    } else {
      tree.addChild(post.id, post.parentPostId, post, post.childrenFolded);
    }
  }
  return tree.treeItems().map(item => ({
    post: item.item,
    treeItem: {
      nestLevel: item.nestLevel,
      hasNextSibling: item.hasNextSibling,
    },
  }));
}

function postOrdering(ordering: PostOrdering): (a, b) => number {
  if (ordering === 'orderByCreationDateNewest') {
    return orderByCreationDateDesc;
  }
  if (ordering === 'orderByCreationDateOldest') {
    return orderByCreationDateAsc;
  }
  return orderByScoreThenCreationDate;
}

function orderByScoreThenCreationDate(a: Post, b: Post): number {
  if (a.score === b.score) {
    return orderByCreationDateDesc(a, b);
  }
  return orderByScoreDesc(a, b);
}

function orderByScoreDesc(a: Post, b: Post): number {
  return b.score - a.score;
}

function orderByCreationDateAsc(a: Post, b: Post): number {
  return a.created_at! > b.created_at! ? 1 : -1;
}

function orderByCreationDateDesc(a: Post, b: Post): number {
  return a.created_at! < b.created_at! ? 1 : -1;
}
