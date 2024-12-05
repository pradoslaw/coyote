export function voteDescription(
  votes: number,
  youVoted: boolean,
  otherVoters: string[] | null,
): string {
  if (votes === 0) {
    return 'Doceń post';
  }
  if (votes === 1 && youVoted) {
    return 'Doceniłeś post';
  }
  if (!otherVoters) {
    return votes + ' użytkowników doceniło post';
  }
  if (youVoted) {
    return joinMultiple(['Ty', ...otherVoters]) + ' doceniliście post';
  }
  if (otherVoters.length === 1) {
    return otherVoters[0] + ' docenił post';
  }
  return joinMultiple(otherVoters) + ' docenili post';
}

function joinMultiple(voters: string[]): string {
  const lastVoter = voters.pop();
  return voters.join(', ') + ' i ' + lastVoter;
}
