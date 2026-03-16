<script setup lang="ts">
import type { PropType } from 'vue';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const SHEET_SIDES = ["top", "right", "bottom", "left"] as const

defineProps({
    sheetTitle: {
        type: String,
        default: null,
    },
    sheetDescription: {
        type: String,
        default: null,
    },

    side: {
        type: String as PropType<typeof SHEET_SIDES[number]>,
        default: 'right',
    },
    contentClasses: {
        type: String,
        default: 'max-w-sm',
    },
});
</script>

<template>
    <Sheet>
        <SheetTrigger as-child>
            <slot name="trigger"/>
        </SheetTrigger>
        <SheetContent :side="side" :class="contentClasses">
            <SheetHeader>
                <SheetTitle>{{ sheetTitle }}</SheetTitle>
                <SheetDescription>
                    {{ sheetDescription }}
                </SheetDescription>
            </SheetHeader>
            <slot></slot>
            <SheetFooter>
<!--                <SheetClose as-child>-->
<!--                    <Button type="submit"> Save changes </Button>-->
<!--                </SheetClose>-->

                <slot name="footer"></slot>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
