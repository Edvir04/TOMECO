<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Open Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  @if(session('success')) <div style="color:green">{{ session('success') }}</div> @endif
  @if($errors->any())
    <div style="color:red">
      <ul>
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('open.register.store') }}">
    @csrf

    <label>Role
      <select name="role" required>
        <option value="superadmin">Superadmin</option>
        <option value="admin">Admin</option>
        <option value="enforcer">Tomeco Enforcer</option>
      </select>
    </label><br>

    <label>Full name
      <input type="text" name="fullname" required>
    </label><br>

    <label>Username
      <input type="text" name="username" required>
    </label><br>

    <label>Password
      <input type="password" name="password" required>
    </label><br>

    <label>Gender
      <select name="gender" required>
        <option value="male">male</option>
        <option value="female">female</option>
        <option value="other">other</option>
      </select>
    </label><br>

    <label>Date of birth
      <input type="date" name="dob" required>
    </label><br>

    <label>Contact number
      <input type="text" name="contact_number" required>
    </label><br>

    <label>Address
      <input type="text" name="address" required>
    </label><br>

    <label>Profile picture (path or URL)
      <input type="text" name="profile_picture">
    </label><br>

    <button type="submit">Create account</button>
  </form>

  <p><a href="{{ route('admin.login') }}">Back to Login</a></p>
</body>
</html>
