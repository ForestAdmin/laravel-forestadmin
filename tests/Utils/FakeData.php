<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Tag;
use Illuminate\Support\Carbon;

/**
 * Class FakeData
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait FakeData
{
    /**
     * @return Book
     */
    public function getBook()
    {
        $category = new Category();
        $category->label = 'bar';
        $category->save();

        $book = new Book();
        $book->label = 'foo';
        $book->comment = 'test value';
        $book->difficulty = 'easy';
        $book->amount = 50.20;
        $book->options = [];
        $book->category_id = $category->id;
        $book->setRelation('category', $category);

        return $book;
    }

    /**
     * @return void
     */
    public function makeBooks(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $book = Book::create(
                [
                    'label'        => 'test book ' . $i + 1,
                    'comment'      => '',
                    'difficulty'   => 'easy',
                    'amount'       => 1000,
                    'options'      => [],
                    'category_id'  => 1,
                    'published_at' => Carbon::today()->subDays(rand(0, 1)),
                ]
            );

            for ($j = 0; $j < $i + 1; $j++) {
                Comment::create(
                    [
                        'body'    => 'Test comment',
                        'user_id' => 1,
                        'book_id' => $book->id,
                    ]
                );
            }

            for ($j = 0; $j < $i + 1; $j++) {
                Range::create(
                    [
                        'label' => 'Test range',
                    ]
                )->books()->save($book);
            }
        }
    }

    /**
     * @return void
     */
    public function getComments(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $comment = new Comment();
            $comment->id = $i + 1;
            $comment->body = 'Test comment';
            $comment->user_id = 1;
            $comment->book_id = 1;
            $comment->save();
        }
    }

    /**
     * @return void
     */
    public function getRanges(): void
    {
        $book = Book::find(1);
        for ($i = 0; $i < 2; $i++) {
            $range = new Range();
            $range->id = $i + 1;
            $range->label = 'Test range';
            $book->ranges()->save($range);
        }
    }

    /**
     * @return void
     */
    public function getTags(): void
    {
        $book = Book::find(1);
        for ($i = 0; $i < 2; $i++) {
            $tag = new Tag();
            $tag->id = $i + 1;
            $tag->label = 'Test range';
            $tag->taggable()->associate($book);
            $tag->save();
        }
    }
}
