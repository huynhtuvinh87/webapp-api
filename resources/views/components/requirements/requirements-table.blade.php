<table>
    <tr>
        <th>
            Company
        </th>
        <th>
            Position
        </th>
        <th>
            Requirement
        </th>
        <th>
            Status
        </th>
    </tr>

    @foreach($requirements as $requirement)
        <tr>
            <td>
                {{ $requirement->hiring_organization_name }}
            </td>
            <td>
                {{ $requirement->position_name }}
            </td>
            <td>
                {{ $requirement->requirement_name }}
            </td>
            <td>
                @if($requirement->requirement_status == 'in_warning')
                    Warning
                    (Due {{ date("D d M Y", strtotime($requirement->due_date)) }})
                @else
                    Expired
                    {{ date("D d M Y", strtotime($requirement->due_date)) }}
                @endif
            </td>
        </tr>
    @endforeach
</table>
