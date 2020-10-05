export const install = (Vue, options) => {
  const identifier = options.identifier;
  const currentTime = new Date().getTime() / 1000;

  Vue.prototype.$saveDraft = (data: string) => {
    try {
      // @ts-ignore
      localStorage.setItem(identifier, JSON.stringify({
        'content': data,
        'timestamp': currentTime
      }));
    }
    catch (e) {
      // @ts-ignore
      localStorage.clear();
    }
  };

  Vue.prototype.$loadDraft = (): string | void => {
    if (!localStorage.getItem(identifier)) {
      return;
    }

    const data = JSON.parse(localStorage.getItem(identifier) as string);

    if (data.timestamp < currentTime - 3600) {
      localStorage.removeItem(identifier);
    }

    return data.content;
  };

  Vue.prototype.$removeDraft = () => {
    localStorage.removeItem(identifier);
  }
};

export default install;
