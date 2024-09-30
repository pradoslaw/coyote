<?php
namespace Database\Seeders;

use Coyote\Events\ForumSaved;
use Coyote\Forum;
use Illuminate\Database\Seeder;

class ForumsTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->forum(
            'Newbie',
            'Jeżeli jesteś kompletnym laikiem jeżeli chodzi o programowanie, to jest odpowiednia kategoria dla Ciebie.',
            'Podstawy programowania');

        $parent = $this->forum(
            'Python',
            'Forum o Pythonie.',
            'Podstawy programowania');

        $this->createForum('Dla początkujących', 'Python/Dla_poczatkujacych', 'Forum o Pythonie dla dla początkujących.', 'Podkategorie', $parent->id);
        $this->createForum('Dla zaawansowanych', 'Python/Dla_zaawansowanych', 'Forum o Pythonie dla zaawansowanych', 'Podkategorie', $parent->id);

        $this->forum(
            'Off-Topic',
            'Miejsce na dyskusje niepasujące do pozostałych kategorii forum, niekoniecznie związane z programowaniem',
            'Inne');
    }

    private function forum(string $nameSlug, string $description, string $section): Forum
    {
        return $this->createForum($nameSlug, $nameSlug, $description, $section);
    }

    private function createForum(string $name, string $slug, string $description, string $section, ?int $parentId = null): Forum
    {
        $forum = Forum::query()->create([
            'name'             => $name,
            'slug'             => $slug,
            'description'      => $description,
            'parent_id'        => $parentId,
            'section'          => $section,
            'enable_anonymous' => false,
        ]);
        event(new ForumSaved($forum));
        return $forum;
    }
}
