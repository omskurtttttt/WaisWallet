<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile Settings" />

    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <!-- Header Row -->
            <div class="animate-fade-in">
                <h1 class="text-2xl font-bold text-foreground">Profile Settings</h1>
                <p class="mt-1 text-sm text-muted-foreground">Manage your personal information, security settings, and account preferences.</p>
            </div>

            <div class="space-y-6 max-w-7xl animate-slide-up" style="animation-delay: 0.1s">
                <div class="glass-card p-4 sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="glass-card p-4 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="glass-card p-4 sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
