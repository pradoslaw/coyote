import {Post, TreePost} from "../types/models";
import {TreeTopicRecords} from "./treeTopicRecords";

export type TreeOrderBy =
  'orderByCreationDateNewest' |
  'orderByCreationDateOldest' |
  'orderByMostLikes';

export type Sorter = (a, b) => number;

export function postsOrdered(posts: Post[], ordering: TreeOrderBy): TreePost[] {
  return postsToTreePosts(posts, postOrdering(ordering));
}

function postsToTreePosts(posts: Post[], sorter: Sorter): TreePost[] {
  const tree = new TreeTopicRecords<Post>(sorter);
  const postsWithChildren = new Set();
  for (const post of posts) {
    if (!post.parentPostId) {
      tree.setRoot(post.id, post);
    } else {
      tree.addChild(post.id, post.parentPostId, post, post.childrenFolded);
      postsWithChildren.add(post.parentPostId);
    }
  }
  return tree.flatTreeItems().map(item => ({
    post: item.item,
    treeItem: {
      nestLevel: item.nestLevel,
      hasNextSibling: item.hasNextSibling,
      hasChildren: postsWithChildren.has(item.item.id),
    },
  }));
}

function postOrdering(ordering: TreeOrderBy): Sorter {
  if (ordering === 'orderByCreationDateNewest') {
    return orderByCreationDateDesc;
  }
  if (ordering === 'orderByCreationDateOldest') {
    return orderByCreationDateAsc;
  }
  return orderByScoreThenCreationDate;
}

function orderByScoreThenCreationDate(a: Post, b: Post): number {
  if (a.orderingScore === b.orderingScore) {
    return orderByCreationDateDesc(a, b);
  }
  return orderByScoreDesc(a, b);
}

function orderByScoreDesc(a: Post, b: Post): number {
  return b.orderingScore - a.orderingScore;
}

function orderByCreationDateAsc(a: Post, b: Post): number {
  return a.created_at! > b.created_at! ? 1 : -1;
}

function orderByCreationDateDesc(a: Post, b: Post): number {
  return a.created_at! < b.created_at! ? 1 : -1;
}
