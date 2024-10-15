@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Soumettre votre Témoignage</h1>

    <form action="{{ route('testimonials.submit.post', ['token' => $testimonialRequest->token]) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="testimonial">Votre Témoignage</label>
            <textarea name="testimonial" id="testimonial" rows="5" class="form-control" required>{{ old('testimonial') }}</textarea>
            @error('testimonial')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Soumettre</button>
    </form>
</div>
@endsection
