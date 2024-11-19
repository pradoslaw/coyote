export function voteDescription(
  votes: number,
  youVoted: boolean,
  voters: string[],
): string {
  if (votes === 2) {
    return 'Ty i ' + voters[0] + ' doceniliście post';
  }
  if (votes === 1) {
    if (youVoted) {
      return 'Doceniłeś post';
    }
    return voters[0] + ' docenił post';
  }
  return 'Doceń post';
}
