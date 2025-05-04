"use client"

import { useState, useCallback } from "react"
import { useDropzone } from "react-dropzone"
import Papa from "papaparse"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import { Progress } from "@/components/ui/progress"
import { Upload, FileText, AlertCircle, CheckCircle2 } from "lucide-react"

export function CSVUploader({ setFile, handleUpload, success, setSuccess, setIsLoading, isLoading }: any) {
    const [fileName, setFileName] = useState<string | null>(null)
    const [error, setError] = useState<string | null>(null)
    const [fileUploaded, setFileUploaded] = useState<boolean>(false)

    const onDrop = useCallback((acceptedFiles: File[]) => {
        const file = acceptedFiles[0]
        setError(null)
        setSuccess(false)

        if (file) {
            if (file.type !== "text/csv" && !file.name.endsWith(".csv")) {
                setError("Please upload a valid CSV file")
                return
            }

            setIsLoading(false)
            setFileName(file.name)
            setFile(file)
            // Validate that it's a proper CSV by parsing it
            Papa.parse(file, {
                header: true,
                complete: () => {
                    setIsLoading(false)
                    setFileUploaded(true)
                },
                error: (error) => {
                    setError(`Error parsing CSV: ${error.message}`)
                    setIsLoading(false)
                    setFileUploaded(false)
                },
            })
        }
    }, [])

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: {
            "text/csv": [".csv"],
        },
        maxFiles: 1,
    })

    const handleReset = () => {
        setFileName(null)
        setError(null)
        setSuccess(false)
        setFileUploaded(false)
        setFile(null)
    }

    return (
        <Card className="w-full mx-auto">
            <CardHeader>
                <CardTitle>CSV File Uploader</CardTitle>
                <CardDescription>Upload your CSV file to import data</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {error && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertTitle>Error</AlertTitle>
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {success && (
                    <Alert className="bg-green-50 border-green-200">
                        <CheckCircle2 className="h-4 w-4 text-green-600" />
                        <AlertTitle className="text-green-800">Success</AlertTitle>
                        <AlertDescription className="text-green-700">
                            Your CSV file has been successfully uploaded and being processed.
                        </AlertDescription>
                    </Alert>
                )}

                {!fileUploaded && (
                    <div
                        {...getRootProps()}
                        className={`border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors ${isDragActive ? "border-primary bg-primary/5" : "border-muted-foreground/20"
                            }`}
                    >
                        <input {...getInputProps()} />
                        <div className="flex flex-col items-center justify-center gap-2">
                            <Upload className="h-10 w-10 text-muted-foreground" />
                            <h3 className="font-medium text-lg">
                                {isDragActive ? "Drop the CSV file here" : "Drag & drop a CSV file here"}
                            </h3>
                            <p className="text-sm text-muted-foreground">or click to browse files (max 10MB)</p>
                            <FileText className="h-16 w-16 text-muted-foreground/50 mt-2" />
                            <Button variant="secondary" className="mt-2">
                                Select CSV File
                            </Button>
                        </div>
                    </div>
                )}

                {isLoading && (
                    <div className="space-y-2">
                        <div className="flex justify-between text-sm">
                            <span>Uploading...</span>
                        </div>
                    </div>
                )}

                {fileUploaded && !isLoading && !success && (
                    <div className="p-4 border rounded-md bg-muted/30">
                        <div className="flex items-center gap-3">
                            <FileText className="h-8 w-8 text-primary" />
                            <div>
                                <p className="font-medium">{fileName}</p>
                                <p className="text-sm text-muted-foreground">Ready to upload</p>
                            </div>
                        </div>
                    </div>
                )}
            </CardContent>

            {fileUploaded && !isLoading && (
                <CardFooter className="flex justify-between">
                    <Button variant="outline" onClick={handleReset}>
                        Reset
                    </Button>
                    <Button onClick={handleUpload} disabled={isLoading || success}>
                        {success ? "Uploaded" : "Upload Data"}
                    </Button>
                </CardFooter>
            )}
        </Card>
    )
}
