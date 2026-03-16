<script setup lang="ts">
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'

defineProps<{
    tooltip?: string | null;
    delay?: number | null;
}>();
</script>

<template>
    <TooltipProvider :delay-duration="delay ?? 0">
        <Tooltip>
            <TooltipTrigger>
                <slot/>
            </TooltipTrigger>
            <TooltipContent>
                <template v-if="tooltip">
                    {{ tooltip }}
                </template>
                <slot v-else name="tooltip"/>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
