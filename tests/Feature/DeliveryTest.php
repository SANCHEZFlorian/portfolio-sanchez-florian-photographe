<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Photo;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_active_delivery_page(): void
    {
        $category = Category::create(['name' => 'Automobile', 'slug' => 'automobile']);
        $project = Project::create([
            'title' => 'Shooting Porsche GT3',
            'slug' => 'shooting-porsche-gt3',
            'category_id' => $category->id,
            'status' => 'draft', // Même en brouillon, la livraison privée doit fonctionner !
            'delivery_enabled' => true,
            'delivery_token' => 'test-token-12345',
            'delivery_title' => 'Remise Shooting Porsche',
            'delivery_message' => 'Voici les clichés du shooting',
        ]);

        $response = $this->get('/livraison/test-token-12345');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/DeliveryShow')
            ->has('project')
            ->where('project.delivery_token', 'test-token-12345')
        );
    }

    public function test_guest_cannot_view_disabled_delivery_page(): void
    {
        $category = Category::create(['name' => 'Paysages', 'slug' => 'paysages']);
        Project::create([
            'title' => 'Shooting Lac',
            'slug' => 'shooting-lac',
            'category_id' => $category->id,
            'delivery_enabled' => false,
            'delivery_token' => 'disabled-token',
        ]);

        $response = $this->get('/livraison/disabled-token');

        $response->assertStatus(404);
    }

    public function test_guest_can_download_zip_archive(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Motos', 'slug' => 'motos']);
        $project = Project::create([
            'title' => 'Shooting Ducati',
            'slug' => 'shooting-ducati',
            'category_id' => $category->id,
            'delivery_enabled' => true,
            'delivery_token' => 'ducati-token-999',
            'delivery_download_count' => 0,
        ]);

        // Simuler un fichier photo réel
        $uploaded = UploadedFile::fake()->image('ducati_01.jpg', 1920, 1080);
        $path = $uploaded->store('projects/' . $project->id, 'public');

        Photo::create([
            'project_id' => $project->id,
            'image_path' => $path,
            'caption' => 'Profil avant',
            'display_order' => 1,
        ]);

        $response = $this->get('/livraison/ducati-token-999/telecharger-zip');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');

        $this->assertEquals(1, $project->fresh()->delivery_download_count);
    }

    public function test_admin_can_regenerate_delivery_token(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Animaux', 'slug' => 'animaux']);
        $project = Project::create([
            'title' => 'Shooting Husky',
            'slug' => 'shooting-husky',
            'category_id' => $category->id,
            'delivery_enabled' => true,
            'delivery_token' => 'initial-token',
        ]);

        $response = $this->actingAs($user)->post("/admin/projects/{$project->id}/regenerate-delivery-token");

        $response->assertRedirect();
        $this->assertNotEquals('initial-token', $project->fresh()->delivery_token);
        $this->assertNotEmpty($project->fresh()->delivery_token);
    }

    public function test_guest_is_prompted_for_password_when_set(): void
    {
        $category = Category::create(['name' => 'Portrait', 'slug' => 'portrait']);
        $project = Project::create([
            'title' => 'Shooting Sarah',
            'slug' => 'shooting-sarah',
            'category_id' => $category->id,
            'delivery_enabled' => true,
            'delivery_token' => 'sarah-token-pwd',
            'delivery_password' => 'Secret123#',
        ]);

        // Première visite sans session déverrouillée : doit renvoyer la vue DeliveryPassword
        $response = $this->get('/livraison/sarah-token-pwd');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/DeliveryPassword')
            ->where('token', 'sarah-token-pwd')
        );
    }

    public function test_guest_fails_unlock_with_invalid_password(): void
    {
        $category = Category::create(['name' => 'Portrait', 'slug' => 'portrait']);
        Project::create([
            'title' => 'Shooting Sarah',
            'slug' => 'shooting-sarah',
            'category_id' => $category->id,
            'delivery_enabled' => true,
            'delivery_token' => 'sarah-token-pwd-fail',
            'delivery_password' => 'Secret123#',
        ]);

        $response = $this->post('/livraison/sarah-token-pwd-fail/deverrouiller', [
            'password' => 'MauvaisMotDePasse',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_guest_can_unlock_and_download_with_valid_password(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Portrait', 'slug' => 'portrait']);
        $project = Project::create([
            'title' => 'Shooting Sarah',
            'slug' => 'shooting-sarah',
            'category_id' => $category->id,
            'delivery_enabled' => true,
            'delivery_token' => 'sarah-token-pwd-ok',
            'delivery_password' => 'MonSuperMotDePasse!',
        ]);

        // Tenter de télécharger le zip avant mot de passe -> 403
        $forbiddenResponse = $this->get('/livraison/sarah-token-pwd-ok/telecharger-zip');
        $forbiddenResponse->assertStatus(403);

        // Déverrouiller avec le bon mot de passe
        $unlockResponse = $this->post('/livraison/sarah-token-pwd-ok/deverrouiller', [
            'password' => 'MonSuperMotDePasse!',
        ]);

        $unlockResponse->assertRedirect('/livraison/sarah-token-pwd-ok');
        $unlockResponse->assertSessionHas('delivery_unlocked_' . $project->id, true);

        // Maintenant l'accès à la galerie est autorisé
        $galleryResponse = $this->get('/livraison/sarah-token-pwd-ok');
        $galleryResponse->assertStatus(200);
        $galleryResponse->assertInertia(fn ($page) => $page
            ->component('Public/DeliveryShow')
        );
    }
}
