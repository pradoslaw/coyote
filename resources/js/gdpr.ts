import axios from "axios";
import {guest} from "./store/modules/user";

const root = rootElement();
if (root) {
  main(root);
}

function main(root: HTMLElement): void {
  if (!gdprSubmitted()) {
    open(root!);
    addGdprSubmitListeners(root);
  }
}

function addGdprSubmitListeners(root: HTMLElement): void {
  click('button#gdpr-none', () => submit(false, false));
  click('button#gdpr-all', () => submit(true, true));
  click('button#gdpr-selected', () => submit(
    element('input#advertising').checked,
    element('input#analytics').checked,
  ));
  click('button.close', () => close(root));
}

function click(selector: string, onClick: () => void): void {
  element(selector).addEventListener('click', onClick);
}

function element(selector: string): HTMLInputElement {
  return <HTMLInputElement>rootElement()!.querySelector(selector);
}

function rootElement(): HTMLElement | null {
  return document.querySelector('.gdpr-modal');
}

function open(root: HTMLElement): void {
  root.style.display = 'block';
}

function close(root: HTMLElement): void {
  root.style.display = 'none';
  storeGdpr();
}

function submit(advertising: boolean, analytics: boolean): void {
  if (guest) {
    close(rootElement()!);
  } else {
    axios
      .put('/User/Privacy', {advertising, analytics})
      .then(() => close(rootElement()!));
  }
}

function gdprSubmitted(): boolean {
  return guest && !!localStorage.getItem('gdpr');
}

function storeGdpr(): void {
  if (guest) {
    localStorage.setItem('gdpr', 'true');
  }
}
