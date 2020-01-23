import declination from '../components/declination';

Date.prototype.asInteger = function () {
  return String(this.getFullYear()) + String(this.getMonth() + 1) + String((this.getDate() < 10 ? '0' : '') + this.getDate());
};

Date.prototype.getHour = function () {
  return (this.getHours() < 10 ? '0' : '') + this.getHours();
};

Date.prototype.getMinute = function () {
  return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes();
};

function getTimeSpan(remote) {
  return Math.abs(Math.round((new Date().getTime() / 1000) - remote));
}

function getDiffMinute(remote) {
  return Math.round(getTimeSpan(remote) / 60);
}

function getDiffSecond(remote) {
  return getTimeSpan(remote);
}

const timeago = (timestamp) => {
  if (getDiffMinute(timestamp) < 60) {
    if (getDiffSecond(timestamp) >= 60) {
      return getDiffMinute(timestamp) + ' ' + declination(getDiffMinute(timestamp), ['minuta', 'minuty', 'minut']) + ' temu';
    }

    return getDiffSecond(timestamp) + ' ' + declination(getDiffSecond(timestamp), ['sekunda', 'sekundy', 'sekund']) + ' temu';
  }

  let currDate = new Date();
  let currValue = currDate.asInteger();

  let spanDate = new Date(timestamp * 1000);
  let spanValue = spanDate.asInteger();

  if (spanValue === currValue) {
    return 'dziś, ' + spanDate.getHour() + ':' + spanDate.getMinute();
  } else if (spanValue === currValue - 1) {
    return 'wczoraj, ' + spanDate.getHour() + ':' + spanDate.getMinute();
  }

  return null;
};

const countTime = () => {
  let dates = document.querySelectorAll('.timestamp[data-timestamp]');

  dates.forEach(date => {
    const timestamp = date.getAttribute('data-timestamp');
    const value = timeago(timestamp);

    if (value) {

      date.textContent = value;
    }
  });
};

setInterval(countTime, 30000); // 30 sek

export default timeago;
