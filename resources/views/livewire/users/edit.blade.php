<?php

use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\Language;
use Livewire\Volt\Component;
use Livewire\WithFileUploads; 

new class extends Component {
    
	use Toast, WithFileUploads;

	public User $user;

    #[Rule('required')] 
    public string $name = '';
 
    #[Rule('required|email')]
    public string $email = '';
 
    // Optional
    #[Rule('sometimes')]
    public ?int $country_id = null;

 	#[Rule('nullable|image|max:1024')] 
	public $photo;

	#[Rule('required')]
	public array $my_languages = [];
 
    // Optional 
    #[Rule('sometimes')]
    public ?string $bio = null;

    // We also need this to fill Countries combobox on upcoming form
    public function with(): array 
    {
        return [
            'countries' => Country::all(),
			'languages' => Language::all()
        ];
    }

	public function mount(): void
	{
		$this->fill($this->user);
		$this->my_languages = $this->user->languages->pluck('id')->all();

	}

	public function save(): void
	{
		// Validate
		$data = $this->validate([
			'name' => ['string', 'required'],
			'email' => ['email', 'required'],
			'bio' => ['nullable']
		]);
	
		// Update
		$this->user->update($data);

		$this->country_id ?? $this->user->country->update($this->country_id);

		$this->user->languages()->sync($this->my_languages);


		if ($this->photo) {
			$url = $this->photo->store('users', 'public');
			$this->user->update(['avatar' => '/storage/'.$url]);
		}
	
		// You can toast and redirect to any route
		$this->success('User updated with success.', redirectTo: '/users');
	}

}; ?>

<div>
    <x-header title="Update {{ $user->name }}" separator />

    <x-form wire:submit="save">

        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Basic" subtitle="Basic info from user" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">

                <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
                </x-file>
                <x-input label="Name" wire:model="name" />
                <x-input label="Email" wire:model="email" />
            </div>
        </div>

        <hr class="my-5" />

        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Details" subtitle="More about the user" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---" />

                <x-choices-offline label="My languages" wire:model="my_languages" :options="$languages" searchable />

                <x-editor wire:model="bio" label="Bio" hint="The great biography" />
            </div>
        </div>
        <x-slot:actions>
            <x-button label="Cancel" link="/users" />
            <x-button label="Save" spinner type="submit" class="btn-primary" />

        </x-slot:actions>
    </x-form>

</div>
