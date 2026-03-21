<x-layouts.app title="Data Deletion Policy | Tradexy">
    <div class="max-w-4xl mx-auto px-6 py-12 lg:py-24">
        <h1 class="text-3xl md:text-5xl font-bold text-gray-900 dark:text-white mb-8">Data Deletion Policy</h1>
        
        <div class="prose dark:prose-invert prose-blue max-w-none space-y-8 text-gray-600 dark:text-gray-400">
            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">1. User-Initiated Data Deletion</h2>
                <p>We respect your right to be forgotten. You have full control over your data stored on Tradexy. You can delete your entire account and all associated data at any time via your Profile Settings.</p>
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-800">
                    <p class="font-medium text-gray-900 dark:text-white mb-2">How to delete your account:</p>
                    <ol class="list-decimal ml-6 space-y-1">
                        <li>Log in to your <strong>Tradexy</strong> account.</li>
                        <li>Navigate to <strong>Profile Settings</strong>.</li>
                        <li>Scroll down to the <strong>Danger Zone</strong>.</li>
                        <li>Confirm and click <strong>Delete Account</strong>.</li>
                    </ol>
                </div>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">2. What Data Is Deleted?</h2>
                <p>When you delete your account, the following data is permanently purged from our active databases:</p>
                <ul class="list-disc ml-6 space-y-2">
                    <li><strong>Personal Information</strong>: Your name, email address, and profile picture.</li>
                    <li><strong>Trade Records</strong>: Every trade log, entry reason, and lesson learned.</li>
                    <li><strong>Strategies</strong>: Your custom defined trading setups and backtesting data.</li>
                    <li><strong>Balance History</strong>: All historical account balance records.</li>
                    <li><strong>Chart Screenshots</strong>: All images stored in our Firebase Storage bucket associated with your account will be permanently purged.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">4. Third-Party Data</h2>
                <p>Once you confirm deletion, your data is removed immediately from our live application. However, encrypted backups of our database (used for disaster recovery) may retain a copy of your data for up to <strong>7 days</strong> before being automatically rotated and overwritten.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">5. Contact for Support</h2>
                <p>If you are unable to access your account or need assistance with a manual data deletion request, please contact us. We will process manual requests within 48 hours of verification.</p>
            </section>

            <section class="pt-8 border-t border-gray-200 dark:border-gray-800">
                <p class="text-sm">Last updated: {{ date('F d, Y') }}</p>
            </section>
        </div>
    </div>
</x-layouts.app>
