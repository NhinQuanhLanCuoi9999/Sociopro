<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Session;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (get_settings('public_signup') != 1) {
            Session::flash('error_message', get_phrase('Public signup not allowed'));
            return redirect()->route('login');
        }
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'user_role' => 'general',
            'username' => rand(100000, 999999),  // Tạo tên người dùng ngẫu nhiên
            'name' => $request->name,
            'email' => $request->email,
            'friends' => json_encode([]),  // Khởi tạo danh sách bạn bè rỗng
            'followers' => json_encode([]),  // Khởi tạo danh sách người theo dõi rỗng
            'timezone' => $request->timezone,
            'password' => Hash::make($request->password),  // Mã hóa mật khẩu
            'status' => 1,  // Cập nhật status thành 1
            'lastActive' => Carbon::now(),  // Thời gian hoạt động cuối cùng
            'email_verified_at' => Carbon::now(),  // Gán ngày giờ hiện tại cho email_verified_at
            'created_at' => time(),  // Thời gian tạo tài khoản
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
?>