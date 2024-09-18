const currentTime = new Date().getTime() / 1000;

function gc() {
  for (let item in localStorage) {
    try {
      const data = JSON.parse(localStorage.getItem(item) as string);
      if (data.timestamp < currentTime - 3600) {
        localStorage.removeItem(item);
      }
    } catch {
    }
  }
}

export function saveDraft(key: string, value: string) {
  try {
    localStorage.setItem(key, JSON.stringify({
      content: value,
      timestamp: currentTime,
    }));
  } catch (e) {
    localStorage.clear();
  }
}

export function loadDraft(key: string): string {
  gc();
  if (!localStorage.getItem(key)) {
    return '';
  }
  const data = JSON.parse(localStorage.getItem(key) as string);
  return data.content;
}

export function removeDraft(key: string) {
  localStorage.removeItem(key);
}
