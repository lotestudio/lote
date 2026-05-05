<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import LoteDatePicker from '@/components/LoteDatePicker.vue';
import LoteSelect from '@/components/LoteSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitialValue } from '@/composables/useInitialFormValue';
import AppLayout from '@/layouts/AppLayout.vue';
import LoteRepeater from '@/components/LoteRepeater.vue';

const page = usePage();
const { get, isEdit } = useInitialValue();

const form = useForm({
    client_id: get('client_id', ''),
    recipient: get('recipient', ''),
    date: get('date', ''),
    services: get('services', []),
});

function submit() {
    if (isEdit) {
        form.put('/admin/invoice/' + page.props.model.id);
    } else {
        form.post('/admin/invoice');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[]">
        <Head :title="isEdit ? 'Edit Invoice' : 'Create Invoice'" />
        <div class="max-w-4xl p-4">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="client_id">Клиент:</Label>
                    <LoteSelect :options="page.props.clientSelect" @change="form.client_id = $event" tabindex="1" :selected="form.client_id" width_class="w-full" />
                    <InputError class="mt-2" :message="form.errors.client_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="date">Дата:</Label>
                    <LoteDatePicker :initial_date="form.date" @change="form.date = $event" tabindex="2" />
                    <InputError class="mt-2" :message="form.errors.date" />
                </div>

                <div class="grid gap-2">
                    <Label for="recipient">Получател:</Label>
                    <Input id="recipient" :tabindex="3" v-model="form.recipient" />
                    <InputError class="mt-2" :message="form.errors.recipient" />
                </div>

                <hr />
                <LoteRepeater :line="{ description: '', value: {number:0}, id: null, items: 1 }" :data="form.services">
                    <template #bar="{ addLine }" v-if="form.services.length === 0">
                        <Button type="button" class="btn btn-sm btn-primary" @click="addLine()"><span class="i-plus"></span></Button>
                    </template>

                    <template #list="{ line, addLine, deleteLine }">
                        <div class="mb-2">
                            <div class="flex w-full items-center gap-2">
                                <Button type="button" class="btn btn-primary btn-sm" @click="addLine()"><span class="i-plus"></span></Button>
                                <div class="flex w-full items-center gap-2">
                                    <Input type="text" class="flex-1" placeholder="Име на услугата" v-model="line.description" @keydown.enter.prevent />
                                    <Input type="number" step="0.01" class="w-37.5 text-right" placeholder="Цена" v-model="line.value.number" @keydown.enter.prevent />
                                    <Input type="number" step="1" class="w-25 text-right" placeholder="Бройки" v-model="line.items" @keydown.enter.prevent />
                                </div>
                                <Button variant="destructive" @click="deleteLine(line.unique_key)"><span class="i-close"></span></Button>
                            </div>
                        </div>
                    </template>
                </LoteRepeater>

                <hr />

                <Button :disabled="form.processing" tabindex="4">Запази</Button>
            </form>
        </div>
    </AppLayout>
</template>
