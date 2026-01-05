/* eslint-disable @typescript-eslint/no-explicit-any */
import apiUtils from '~utils/api-utils';
import request from '../config/api';
import { API_HOST } from '../config/env';
import { ApiResponseWithData } from '../models/response';
import { Document } from '../models/document';
import tokenUtils from '../utils/token-utils';

const prefix = 'documents';

export async function apiGetDocuments(search?: string) {
    try {
        const apiPath = prefix;
        const res = await request.get(apiPath, {
            params: {
                search: search || ''
            }
        });
        const { data } = res.data as ApiResponseWithData<Document[]>;
        return data;
    } catch (error: any) {
        apiUtils.handleError(error);
        throw error;
    }
}

export async function apiGetDocumentById(id: string | number) {
    try {
        const apiPath = `${prefix}/${id}`;
        const res = await request.get(apiPath);
        const { data } = res.data as ApiResponseWithData<Document>;
        return data;
    } catch (error: any) {
        return apiUtils.handleError(error);
    }
}

export async function apiCreateDocument(formData: FormData) {
    try {
        const apiPath = prefix;
        await request.post(apiPath, formData);
    } catch (error: any) {
        apiUtils.handleError(error);
        throw error;
    }
}

export async function apiUpdateDocument(formData: FormData, id: string | number) {
    try {
        const apiPath = `${prefix}/${id}`;
        await request.put(apiPath, formData);
    } catch (error: any) {
        return apiUtils.handleError(error);
    }
}

export async function apiDeleteDocument(id: string | number) {
    try {
        const apiPath = `${prefix}/${id}`;
        await request.delete(apiPath);
    } catch (error: any) {
        return apiUtils.handleError(error);
    }
}

export function getDocumentViewUrl(id: string | number, useWebRoute: boolean = false): string {
    const token = tokenUtils.getToken();
    if (!token) {
        console.error('No token found for document view');
        // Fallback to API route if no token (will fail but at least won't break)
        return `${API_HOST}/api/${prefix}/${id}/view`;
    }
    
    // Nếu mở trong tab mới (useWebRoute = true), sử dụng web route với HTML wrapper có bảo vệ
    // Nếu dùng trong iframe (useWebRoute = false), sử dụng API route trực tiếp
    if (useWebRoute) {
        return `${API_HOST}/documents/${id}/view?token=${encodeURIComponent(token)}`;
    }
    // Sử dụng API route với token trong query string để tránh lỗi 401 khi mở trong iframe
    return `${API_HOST}/api/${prefix}/${id}/view?token=${encodeURIComponent(token)}`;
}

export function getDocumentDownloadUrl(id: string | number): string {
    return `${request.defaults.baseURL}${prefix}/${id}/download`;
}

