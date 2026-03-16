<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useInitialValue } from '@/composables/useInitialFormValue';
import AppLayout from '@/layouts/AppLayout.vue';

const page = usePage();
const { get, isEdit } = useInitialValue();

const form = useForm({
    company: get('company', ''),
    address_1: get('address_1', ''),
    address_2: get('address_2', ''),
    number: get('number', ''),
    vat: get('vat', ''),
    mol: get('mol', ''),
});

function submit() {
    if (isEdit) {
        form.put('/admin/client/' + page.props.model.id);
    } else {
        form.post('/admin/client');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[]">
        <Head :title="isEdit ? 'Edit Client' : 'Create Client'" />
        <div class="max-w-xl p-4">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="company">Company:</Label>
                    <Input
                        id="company"
                        required
                        :tabindex="1"
                        v-model="form.company"
                    />
                    <InputError class="mt-2" :message="form.errors.company" />
                </div>
                <div class="grid gap-2">
                    <Label for="address_1">Address 1:</Label>
                    <Textarea
                        id="company"
                        required
                        :tabindex="2"
                        v-model="form.address_1"
                    />
                    <InputError class="mt-2" :message="form.errors.company" />
                </div>
                <div class="grid gap-2">
                    <Label for="address_2">Address 1:</Label>
                    <Textarea
                        id="company"
                        required
                        :tabindex="2"
                        v-model="form.address_2"
                    />
                    <InputError class="mt-2" :message="form.errors.company" />
                </div>
                <div class="grid gap-2">
                    <Label for="number">EIK:</Label>
                    <Input
                        id="number"
                        required
                        type="number"
                        :tabindex="3"
                        v-model="form.number"
                    />
                    <InputError class="mt-2" :message="form.errors.number" />
                </div>
                <div class="grid gap-2">
                    <Label for="vat">Vat Number:</Label>
                    <Input
                        id="vat"
                        required
                        :tabindex="4"
                        v-model="form.vat"
                    />
                    <InputError class="mt-2" :message="form.errors.vat" />
                </div>
                <div class="grid gap-2">
                    <Label for="mol">Mol:</Label>
                    <Input
                        id="mol"
                        required
                        :tabindex="4"
                        v-model="form.mol"
                    />
                    <InputError class="mt-2" :message="form.errors.mol" />
                </div>

                <Button :disabled="form.processing" tabindex="6">Запази</Button>
            </form>
        </div>
    </AppLayout>
</template>
