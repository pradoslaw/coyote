export default function isImage(filename: string) {
  const suffix = filename.split('.').pop()!.toLowerCase();

  return ['png', 'jpg', 'jpeg', 'gif'].includes(suffix);
}
