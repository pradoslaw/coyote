import axios from "axios";

window.addEventListener('load', function () {
  const questions = document.getElementsByClassName('sem:questionnaireQuestion') as HTMLCollectionOf<HTMLElement>;
  if (questions.length === 0) {
    return;
  }
  const [question] = questions;
  const answers = document.getElementsByClassName('sem:questionnaireAnswer');
  for (const answer of answers) {
    answer.addEventListener('click', function () {
      axios.post('/Questionnaire', {questionnaireAnswer: answer.textContent!.trim()});
      question.remove();
    });
  }
  const closeButtons = document.getElementsByClassName('sem:questionnaireClose');
  for (const close of closeButtons) {
    close.addEventListener('click', function () {
      axios.post('/Questionnaire', {questionnaireAnswer: 'close'});
      question.remove();
    });
  }
  question.style.visibility = 'visible';
  // setTimeout(() => {
  //   question.style.visibility = 'visible';
  //   axios.post('/Questionnaire/See', {});
  // }, 10 * 1000);
});
