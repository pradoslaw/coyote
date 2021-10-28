export const install = (Vue) => {
  const currentTime = new Date().getTime() / 1000;

  function gc() {
    for (let item in localStorage) {
      try {
        const data = JSON.parse(localStorage.getItem(item) as string);

        if (data.timestamp < currentTime - 3600) {
          localStorage.removeItem(item);
        }
      }
      catch {
        //
      }
    }
  }

  Vue.prototype.$saveDraft = (key: string, value: string) => {
    try {
      // @ts-ignore
      localStorage.setItem(key, JSON.stringify({
        'content': value,
        'timestamp': currentTime
      }));
    }
    catch (e) {
      // @ts-ignore
      localStorage.clear();
    }
  };

  Vue.prototype.$loadDraft = (key: string): string => {
    gc();

    if (!localStorage.getItem(key)) {
      return '';
    }

    const data = JSON.parse(localStorage.getItem(key) as string);

    return data.content;
  };

  Vue.prototype.$removeDraft = (key: string) => {
    localStorage.removeItem(key);
  }
};

export default install;
