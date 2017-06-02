@component('mail::message')
@if(!empty($data['requested_reviews']))
@foreach($data['requested_reviews'] as $review)
### Code reviews assigned to {{ $review['reviewer_name'] }}
@foreach($review['pull_requests'] as $pullRequest)
- **{{ $pullRequest['author_name'] }}** assigned [{{ $pullRequest['title'] }}]({{ $pullRequest['url'] }}) on {{ $pullRequest['repository_name'] }}
@endforeach

@endforeach
@else
Looks like there is nothing assigned for code review today.
@endif
@endcomponent
