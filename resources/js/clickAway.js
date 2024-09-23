const HANDLER = '_clickAwayHandler';

function bind(el, binding, vNode) {
  unbind(el);

  const callback = binding.value;
  const vm = vNode.context;

  el[HANDLER] = function (ev) {
    const path = ev.path || (ev.composedPath ? ev.composedPath() : undefined);
    if (path ? path.indexOf(el) < 0 : !el.contains(ev.target)) {
      return callback.call(vm, ev);
    }
  };

  document.documentElement.addEventListener('click', el[HANDLER], false);
}

function unbind(el) {
  document.documentElement.removeEventListener('click', el[HANDLER], false);
  delete el[HANDLER];
}

export default {
  beforeMount: bind,
  updated(el, binding) {
    if (binding.value === binding.oldValue) return;
    bind(el, binding);
  },
  unmounted: unbind,
};
