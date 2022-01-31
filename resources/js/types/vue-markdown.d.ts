interface VueMarkdown {
    appendBlockQuote(username: string, postId: number, content: string): void;

    appendUserMention(username: string): void;
}
