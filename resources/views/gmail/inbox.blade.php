<table>
    <thead>
        <tr>
            <th>Người gửi</th>
            <th>Chủ đề</th>
            <th>Thời gian</th>
            <th>Tóm tắt</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($emails as $email)
            <tr>
                <td>{{ is_array($email['from']) ? implode(', ', $email['from']) : $email['from'] }}</td>
                <td>{{ is_array($email['subject']) ? implode(', ', $email['subject']) : $email['subject'] }}</td>
                <td>{{ $email['date'] }}</td>
                <td>{{ is_array($email['snippet']) ? implode(', ', $email['snippet']) : $email['snippet'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
