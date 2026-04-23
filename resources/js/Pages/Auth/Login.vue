<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion" />

        <div v-if="status" class="badge badge-success mb-4">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="animate-fade-in">
            <div class="form-group">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="votre@email.com"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div class="form-group">
                <InputLabel for="password" value="Mot de passe" />

                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError :message="form.errors.password" />
            </div>

            <div class="flex-between" style="margin-top: var(--space-xl);">
                <Checkbox name="remember" v-model:checked="form.remember">
                    <span style="font-size: 0.85rem; color: var(--text-muted); transition: color 0.3s ease;">Se souvenir de moi</span>
                </Checkbox>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    style="font-size: 0.85rem; color: var(--text-muted); text-decoration: underline; transition: color 0.3s ease;"
                    onmouseover="this.style.color='var(--primary-light)'"
                    onmouseout="this.style.color='var(--text-muted)'"
                >
                    Mot de passe oublié ?
                </Link>
            </div>

            <div style="margin-top: var(--space-2xl);">
                <PrimaryButton
                    style="width: 100%;"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Connexion en cours...' : 'Se connecter' }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

