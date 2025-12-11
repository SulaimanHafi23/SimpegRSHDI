{{-- filepath: resources/views/layouts/partials/admin-footer.blade.php --}}
<footer class="bg-white border-t border-gray-200 px-6 py-4">
    <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <div class="flex space-x-4 mt-2 md:mt-0">
            <a href="#" class="hover:text-primary-600 transition duration-200">Documentation</a>
            <span class="text-gray-400">•</span>
            <a href="#" class="hover:text-primary-600 transition duration-200">Support</a>
            <span class="text-gray-400">•</span>
            <a href="#" class="hover:text-primary-600 transition duration-200">Privacy Policy</a>
        </div>
    </div>
</footer>
