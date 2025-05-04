
import { CSVUploader } from '@/components/csv-uploader';
import { DataTable } from '@/components/data-table/data-table';
import { Head, useForm } from '@inertiajs/react';
import { columns } from "./columns"
import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import echo from '@/echo';

type FormData = {
    file: File | null;
};

export default function Welcome({ uploads }: any) {
    const [file, setFile] = useState<File | null>(null)
    const [success, setSuccess] = useState(false)
    const [isLoading, setIsLoading] = useState(false)

    const { setData, post } = useForm<FormData>({
        file: null
    })

    useEffect(() => {
        if (!file) return
        setData('file', file)
    }, [file])

    useEffect(() => {
        const channel = echo.channel('uploads');

        channel.listen('.upload.status.updated', (e: any) => {
            router.reload({ only: ['uploads'] });
        });

        return () => {
            echo.leave('uploads');
        };
    }, []);

    const handleUpload = () => {
        if (!file) return
        setIsLoading(true)

        post(`/upload`, {
            onSuccess: () => {
                setIsLoading(false)
                setSuccess(true)
            },
        })
    }

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                    <div className="p-4">
                        <CSVUploader setFile={setFile} handleUpload={handleUpload} success={success} setSuccess={setSuccess} setIsLoading={setIsLoading} isLoading={isLoading} />
                    </div>

                    <div className='p-4'>
                        <DataTable
                            columns={columns}
                            data={uploads?.data}
                            showPagination={false}
                        />
                    </div>
                </div>
            </div>
        </>
    );
}
