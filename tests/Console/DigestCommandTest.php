<?php

namespace Canvas\Tests\Console;

use Canvas\Mail\WeeklyDigest;
use Canvas\Models\Post;
use Canvas\Models\UserMeta;
use Canvas\Models\View;
use Canvas\Models\Visit;
use Canvas\Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class DigestCommandTest extends TestCase
{
    /** @test */
    public function an_email_can_be_sent()
    {
        Mail::fake();

        $user = factory(config('canvas.user'))->create();

        factory(UserMeta::class)->create([
            'user_id' => $user->id,
            'digest' => 1,
        ]);

        $posts = factory(Post::class, 2)->create([
            'user_id' => $user->id,
            'published_at' => now()->subWeek(),
        ]);

        foreach ($posts as $post) {
            $post->views()->createMany(
                factory(View::class, 2)->make()->toArray()
            );

            $post->visits()->createMany(
                factory(Visit::class, 1)->make()->toArray()
            );
        }

        $this->artisan('canvas:digest');

        Mail::assertSent(WeeklyDigest::class, function ($mail) use ($user) {
            $this->assertArrayHasKey('posts', $mail->data);
            $this->assertIsArray($mail->data['posts']);

            $this->assertArrayHasKey('views_count', $mail->data['posts'][0]);
            $this->assertArrayHasKey('visits_count', $mail->data['posts'][0]);

            $this->assertArrayHasKey('totals', $mail->data);
            $this->assertSame(4, $mail->data['totals']['views']);
            $this->assertSame(2, $mail->data['totals']['visits']);

            $this->assertArrayHasKey('startDate', $mail->data);
            $this->assertArrayHasKey('endDate', $mail->data);
            $this->assertArrayHasKey('locale', $mail->data);

            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function an_email_will_not_be_sent_if_digest_is_disabled()
    {
        Mail::fake();

        $user = factory(config('canvas.user'))->create();

        factory(UserMeta::class)->create([
            'user_id' => $user->id,
            'digest' => 0,
        ]);

        $posts = factory(Post::class, 2)->create([
            'user_id' => $user->id,
            'published_at' => now()->subWeek(),
        ]);

        foreach ($posts as $post) {
            $post->views()->createMany(
                factory(View::class, 2)->make()->toArray()
            );

            $post->visits()->createMany(
                factory(Visit::class, 1)->make()->toArray()
            );
        }

        $this->artisan('canvas:digest');

        Mail::assertNothingSent();
    }
}
