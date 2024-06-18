<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;


#[Title("Register")]
class RegisterPage extends Component
{
    public $name;
    public $email;
    public $password;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
    ];

    public function submit()
    {
        $this->validate();

      $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        session()->flash('success', 'Account created successfully!');

        auth()->login($user);

        //return redirect()->route('login');

        return redirect()->intended();
    }



    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
