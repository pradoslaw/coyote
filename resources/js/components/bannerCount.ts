import axios from 'axios';

function incrementSetting(key: string): void {
  axios.post('/Settings/Ajax', {key});
}

Array
  .from(document.querySelectorAll('.homepage-ads > img'))
  .forEach((element: Element): void => {
    element.addEventListener('click', () => {
      const {url, key} = banner(element as HTMLElement);
      key && incrementSetting(key);
      url && window.open(url, '_blank');
    });
  });

function banner(element: HTMLElement): Banner {
  return {
    key: getOrNull(element.dataset, 'key'),
    url: getOrNull(element.dataset, 'url'),
  }
}

interface Banner {
  url: string | null,
  key: string | null,
}

function getOrNull(dataset: DOMStringMap, key: string): string | null {
  if (dataset[key]) {
    return dataset[key] ?? null;
  }
  return null;
}
