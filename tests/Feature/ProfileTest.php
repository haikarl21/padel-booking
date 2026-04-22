<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_profile_with_avatar()
    {
        Storage::fake('public');

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'avatar' => null,
        ]);

        $this->actingAs($user);

        // GD extension may not be available in this environment, so fake as regular file
        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->post(route('admin.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHas('success');

        $user->refresh();

        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
        // storage facade returns FilesystemAdapter; document for static analysis
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($user->avatar);
    }
}
