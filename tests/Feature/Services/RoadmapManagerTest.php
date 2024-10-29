<?php

namespace Tests\Feature\Services;

use App\Models\RoadmapItem;
use App\Services\RoadmapManager;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTest;

class RoadmapManagerTest extends FeatureTest
{
    public function test_create(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $roadmapManager = app()->make(RoadmapManager::class);

        $title = 'New Roadmap Item';
        $description = 'Description of the new roadmap item';
        $type = 'feature';

        $roadmapItem = $roadmapManager->createItem($title, $description, $type);

        $this->assertDatabaseHas('roadmap_items', [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'user_id' => $user->id,
            'upvotes' => 1,
            'status' => 'pending_approval',
        ]);

        $this->assertDatabaseHas('roadmap_item_user_upvotes', [
            'user_id' => $user->id,
            'roadmap_item_id' => $roadmapItem->id,
        ]);
    }

    public function test_is_upvotable()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $roadmapManager = app()->make(RoadmapManager::class);

        $title = 'New Roadmap Item';
        $description = 'Description of the new roadmap item';
        $type = 'feature';

        $roadmapItem = $roadmapManager->createItem($title, $description, $type);

        $this->assertTrue($roadmapManager->isUpvotable($roadmapItem));

        $roadmapItem->status = 'completed';
        $roadmapItem->save();

        $this->assertFalse($roadmapManager->isUpvotable($roadmapItem));

        $roadmapItem->status = 'cancelled';

        $this->assertFalse($roadmapManager->isUpvotable($roadmapItem));

    }

    public function test_upvote()
    {
        $user1 = $this->createUser();
        $this->actingAs($user1);

        $roadmapManager = app()->make(RoadmapManager::class);

        $title = 'New Roadmap Item';
        $description = 'Description of the new roadmap item';
        $type = 'feature';

        $roadmapItem = $roadmapManager->createItem($title, $description, $type);

        $user2 = $this->createUser();
        $this->actingAs($user2);

        $roadmapManager->upvote($roadmapItem->id);

        $this->assertDatabaseHas('roadmap_item_user_upvotes', [
            'user_id' => $user2->id,
            'roadmap_item_id' => $roadmapItem->id,
        ]);

        $this->assertEquals(2, $roadmapItem->userUpvotes()->count());

        $this->assertDatabaseHas('roadmap_items', [
            'id' => $roadmapItem->id,
            'upvotes' => 2,
        ]);

        // make sure the user can't upvote the same item twice

        $roadmapManager->upvote($roadmapItem->id);

        $this->assertEquals(2, $roadmapItem->userUpvotes()->count());
    }

    public function test_upvote_unauthenticated()
    {
        $user1 = $this->createUser();

        $roadmapManager = app()->make(RoadmapManager::class);

        $title = 'New Roadmap Item';
        $description = 'Description of the new roadmap item';
        $type = 'feature';

        $roadmapItem = RoadmapItem::create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => $description,
            'type' => $type,
            'user_id' => $user1->id,
            'upvotes' => 1,
            'status' => 'approved',
        ]);

        $roadmapManager->upvote($roadmapItem->id);

        $this->assertDatabaseHas('roadmap_items', [
            'id' => $roadmapItem->id,
            'user_id' => $user1->id,
            'upvotes' => 1,
        ]);
    }

    public function test_remove_upvote_unauthenticated()
    {
        $user1 = $this->createUser();

        $roadmapManager = app()->make(RoadmapManager::class);

        $title = 'New Roadmap Item';
        $description = 'Description of the new roadmap item';
        $type = 'feature';

        $roadmapItem = RoadmapItem::create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => $description,
            'type' => $type,
            'user_id' => $user1->id,
            'upvotes' => 1,
            'status' => 'approved',
        ]);

        $roadmapManager->removeUpvote($roadmapItem->id);

        $this->assertDatabaseHas('roadmap_items', [
            'id' => $roadmapItem->id,
            'user_id' => $user1->id,
            'upvotes' => 1,
        ]);
    }

    public function test_remove_upvote()
    {
        $user1 = $this->createUser();
        $this->actingAs($user1);

        $roadmapManager = app()->make(RoadmapManager::class);

        $title = 'New Roadmap Item';
        $description = 'Description of the new roadmap item';
        $type = 'feature';

        $roadmapItem = $roadmapManager->createItem($title, $description, $type);

        $user2 = $this->createUser();
        $this->actingAs($user2);

        $roadmapManager->upvote($roadmapItem->id);

        $this->assertDatabaseHas('roadmap_item_user_upvotes', [
            'user_id' => $user2->id,
            'roadmap_item_id' => $roadmapItem->id,
        ]);

        $this->assertEquals(2, $roadmapItem->userUpvotes()->count());

        $this->assertDatabaseHas('roadmap_items', [
            'id' => $roadmapItem->id,
            'upvotes' => 2,
        ]);

        $roadmapManager->removeUpvote($roadmapItem->id);

        $this->assertEquals(1, $roadmapItem->userUpvotes()->count());

        $this->assertDatabaseMissing('roadmap_item_user_upvotes', [
            'user_id' => $user2->id,
            'roadmap_item_id' => $roadmapItem->id,
        ]);

        $this->assertDatabaseHas('roadmap_items', [
            'id' => $roadmapItem->id,
            'upvotes' => 1,
        ]);

    }

    public function test_has_user_upvoted()
    {
        $user1 = $this->createUser();
        $this->actingAs($user1);

        $roadmapManager = app()->make(RoadmapManager::class);

        $title = 'New Roadmap Item';
        $description = 'Description of the new roadmap item';
        $type = 'feature';

        $roadmapItem = $roadmapManager->createItem($title, $description, $type);

        $user2 = $this->createUser();
        $this->actingAs($user2);

        $roadmapManager->upvote($roadmapItem->id);

        $this->assertTrue($roadmapManager->hasUserUpvoted($roadmapItem));

        $user3 = $this->createUser();
        $this->actingAs($user3);

        $this->assertFalse($roadmapManager->hasUserUpvoted($roadmapItem));

    }
}
