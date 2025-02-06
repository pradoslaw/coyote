import axios from "axios";

window.addEventListener('load', function () {
  listen('.jobboard-add-offer-list', () => {
    postJobBoardMilestone('add-offer-list');
  });
  listen('.jobboard-add-offer-landing-1', () => {
    postJobBoardMilestone('add-offer-landing-1');
  });
  listen('.jobboard-add-offer-landing-2', () => {
    postJobBoardMilestone('add-offer-landing-2');
  });
});

function listen(cssSelector: string, listener: () => void): void {
  document.querySelectorAll(cssSelector).forEach(element => {
    element.addEventListener('click', () => listener());
  });
}

export function postJobBoardMilestone(milestone: string): void {
  axios.post('/JobBoard/Milestone', {milestone});
}
