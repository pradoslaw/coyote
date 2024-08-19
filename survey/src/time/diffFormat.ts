export default function (seconds: number): string {
  return difference(Math.max(seconds + 59, 0));
}

function difference(seconds: number) {
  const secondsInHour = 60 * 60;
  const secondsInDay = 24 * secondsInHour;
  const secondsInWeek = 7 * secondsInDay;

  const minutes = Math.floor((seconds % secondsInHour) / 60);
  const hours = Math.floor((seconds % secondsInDay) / secondsInHour);
  const days = Math.floor((seconds % secondsInWeek) / secondsInDay);
  const weeks = Math.floor(seconds / secondsInWeek);

  const formattedHours = String(hours).padStart(2, '0');
  const formattedMinutes = String(minutes).padStart(2, '0');

  return `${weeks}t:${days}d:${formattedHours}h:${formattedMinutes}min`;
}
