<!DOCTYPE html>
<html>
<body>
<h2>New Contact Request</h2>
<table>
    <tr><td><b>Name:</b></td><td>{{ $data['name']}}</td></tr>
    <tr><td><b>Phone:</b></td><td>{{ $data['phone'] }}</td></tr>
    <tr><td><b>Email:</b></td><td>{{ $data['email'] }}</td></tr>
    <tr><td><b>Request:</b></td><td>{{ $data['request_content'] }}</td></tr>
</table>
</body>
</html>
