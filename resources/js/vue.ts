import Vue from "vue";

export function nextTick(block: () => void): void {
  Vue.nextTick(block);
}
