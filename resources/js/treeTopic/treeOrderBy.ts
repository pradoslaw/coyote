import {Post} from "../types/models";

export type TreeOrderBy =
  'orderByCreationDateNewest' |
  'orderByCreationDateOldest' |
  'orderByMostLikes';

export type Sorter = (a, b) => number;

export function postOrdering(ordering: TreeOrderBy): Sorter {
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
