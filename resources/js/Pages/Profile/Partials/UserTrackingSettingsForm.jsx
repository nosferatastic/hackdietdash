import { useRef } from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import { usePage } from '@inertiajs/react';
import Button from '@mui/material/Button';

export default function UserTrackingSettingsForm({ className = '' }) {

    const user = usePage().props.auth.user;

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Weight Tracking Settings</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Below, you can manage your tracking start date, along with the option to clear all existing weight data.
                </p>

                **TODO**

            </header>
        </section>
    );
}
