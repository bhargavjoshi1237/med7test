<footer class="bg-neutral-900 text-white text-sm">
    <!-- Top Footer Links -->
    <div class="mx-auto lg:px-24 py-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 md:px-6 px-4">
        <!-- Column 1 -->
        <div class="flex items-center h-full">
            <x-brand.logo class="h-auto w-auto mb-4 text-white" />
        </div>
        
        <!-- Column 2 -->
        <div>
            <h3 class="font-bold mb-4 tracking-wide text-xl">Information</h3>
            <ul class="space-y-2">
                <li><a href="#" class="hover:text-sky-400 font-semibold text-md">Hempzorb81™</a></li>
                <li><a href="#" class="hover:text-sky-400 font-semibold text-md">Batch Results</a></li>
                <li><a href="#" class="hover:text-sky-400 font-semibold text-md">The Science</a></li>
                <li><a href="#" class="hover:text-sky-400 font-semibold text-md">Shipping and Returns</a></li>
            </ul>
        </div>
        
        <!-- Column 3 -->
        <div>
            <h3 class="font-bold mb-4 tracking-wide text-xl">Affiliate Program</h3>
            <ul class="space-y-2">
                <li><a href="{{ route('affiliate.portal') }}" class="hover:text-sky-400 text-md font-semibold">Med 7 Provider Affiliate Register</a></li>
                <li><a href="{{ route('affiliate.portal') }}" class="hover:text-sky-400 text-md font-semibold">Med 7 Provider Affiliate Login</a></li>
            </ul>
        </div>
        
        <!-- Column 4 -->
        <div>
            <h3 class="font-bold mb-4 tracking-wide text-xl">Contact</h3>
            <ul class="space-y-2">
                <li class="hover:text-sky-500">
                    <a href="tel:+8015774223" target="_blank">
                        <i class="fa fa-phone fa-lg pr-2" aria-hidden="true"></i>
                        801.577.4223 (4CBD)
                    </a>
                </li>
                <li class="hover:text-sky-500">
                    <a href="mailto:customercare@med7cbd.com" target="_blank">
                        <i class="fa fa-envelope-o pr-2" aria-hidden="true"></i>
                        customercare@med7cbd.com
                    </a>
                </li>
                <li class="hover:text-sky-500">
                    <a href="http://www.med7cbd.com/" target="_blank">
                        <i class="fa fa-globe fa-lg pr-2" aria-hidden="true"></i>
                        www.med7cbd.com
                    </a>
                </li>
                <li class="flex items-center space-x-2 pt-2">
                    <a href="https://www.facebook.com/officialmed7cbd" aria-label="Facebook" target="_blank">
                        <i class="fa fa-facebook-square text-sky-400 fa-lg hover:text-white cursor-pointer"></i>
                    </a>
                    <a href="https://www.instagram.com/officialmed7cbd" aria-label="Instagram" target="_blank">
                        <i class="fa fa-instagram text-sky-400 fa-lg hover:text-white cursor-pointer"></i>
                    </a>
                    <a href="#" aria-label="Twitter" target="_blank">
                        <i class="fa fa-twitter text-sky-400 fa-lg hover:text-white cursor-pointer"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="bg-black px-4 py-10">
        <div class="max-w-6xl mx-auto flex flex-col items-center space-y-4">
            <p class="text-xs text-center mb-2">
                FDA DISCLAIMER: The statements made regarding these products have not been evaluated by the Food and Drug Administration. The efficacy of these products has not been confirmed by FDA approved research. These products are not intended to diagnose, treat, cure or prevent any disease. All information presented here is not meant as a substitute for or alternative to information from health care practitioners. Please consult your health care professional about potential interactions or other possible complications before using any product. The Federal Food, Drug, and Cosmetic Act require this notice.
            </p>
            <p class="text-xs mb-2">Med 7 © {{ now()->year }} · All Rights Reserved</p>
            <div class="flex space-x-4">
                <a href="#" class="text-xs hover:text-sky-400 font-bold">SHIPPING AND RETURNS</a>
                <a href="#" class="text-xs hover:text-sky-400 font-bold">TERMS & USE</a>
                <a href="#" class="text-xs hover:text-sky-400 font-bold">PRIVACY POLICY</a>
            </div>
        </div>
    </div>
</footer>
