import appStyles from '~styles/App.module.css';
import styles from '~styles/CardPage.module.css';

import { useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect, useState } from 'react';
import { LuFileText } from 'react-icons/lu';
import { RiAddFill } from 'react-icons/ri';
import { Link, Navigate, useSearchParams } from 'react-router';
import { apiGetDocuments } from '~api/document';
import Loading from '~components/Loading';
import QUERY_KEYS from '~constants/query-keys';
import useAppContext from '~hooks/useAppContext';
import useDebounce from '~hooks/useDebounce';
import useLanguage from '~hooks/useLanguage';
import css from '~utils/css';
import CreateDocument from './components/CreateDocument';

export default function Documents() {
    const { permissions, appTitle } = useAppContext();
    const [searchParams, setSearchParams] = useSearchParams();
    const [searchQuery, setSearchQuery] = useState(searchParams.get('search') || '');
    const queryDebounce = useDebounce(searchQuery);
    const language = useLanguage('page.documents');
    const [showCreatePopUp, setShowCreatePopUp] = useState(false);
    const queryClient = useQueryClient();
    const queryData = useQuery({
        queryKey: [QUERY_KEYS.PAGE_DOCUMENTS, { search: queryDebounce }],
        queryFn: () => apiGetDocuments(queryDebounce),
        enabled: permissions.has('document_view'),
        staleTime: 0,
        refetchOnWindowFocus: false
    });
    useEffect(() => {
        if (!searchParams.get('search') && !queryDebounce) return;
        if (queryDebounce === '') searchParams.delete('search');
        else searchParams.set('search', queryDebounce);
        setSearchParams(searchParams);
    }, [queryDebounce, searchParams, setSearchParams]);
    const onMutateSuccess = async () => {
        await queryClient.invalidateQueries({ queryKey: [QUERY_KEYS.PAGE_DOCUMENTS] });
        await queryClient.refetchQueries({ 
            queryKey: [QUERY_KEYS.PAGE_DOCUMENTS],
            type: 'active'
        });
    };
    useEffect(() => {
        if (language) appTitle.setAppTitle(language.documents || 'Tài liệu');
    }, [appTitle, language]);
    if (!permissions.has('document_view')) return <Navigate to='/' />;
    return (
        <>
            {showCreatePopUp === true ?
                <CreateDocument
                    onMutateSuccess={async () => {
                        await onMutateSuccess();
                        setTimeout(() => {
                            queryData.refetch();
                        }, 200);
                    }}
                    setShowPopUp={setShowCreatePopUp}
                /> : null}
            <main className={appStyles.dashboard}>
                {
                    permissions.hasAnyFormList(['document_create'])
                        ?
                        <section className={appStyles.actionBar}>
                            {
                                permissions.has('document_create') ?
                                    <button
                                        className={appStyles.actionItem}
                                        onClick={() => {
                                            setShowCreatePopUp(true);
                                        }}
                                    >
                                        <RiAddFill /> {language?.add || 'Thêm mới'}
                                    </button>
                                    : null
                            }
                        </section>
                        : null
                }
                <section className={styles.pageContent}>
                    {
                        queryData.isLoading ? <Loading /> : null
                    }
                    <div className={styles.filterForm}>
                        <div className={styles.wrapInputItem}>
                            <label>{language?.filter?.search || 'Tìm kiếm'}</label>
                            <input
                                onInput={(e) => {
                                    setSearchQuery(e.currentTarget.value);
                                }}
                                defaultValue={queryDebounce}
                                className={css(appStyles.input, styles.inputItem)}
                            />
                        </div>
                    </div>
                    <div className={styles.wrapCardContainer}>
                        <div className={styles.cardContainer}>
                            {queryData.data && queryData.data.length > 0 ? (
                                queryData.data.map(item => {
                                    return (
                                        <Link
                                            key={`document-${item.id}`}
                                            to={String(item.id)}
                                            className={css(appStyles.dashboardCard, styles.card)}
                                        >
                                            <div className={styles.cardTop}>
                                                <p className={styles.content}>
                                                    {item.title}
                                                </p>
                                            </div>
                                            <div className={styles.cardBottom}>
                                                <LuFileText />
                                                {item.file_name} {item.file_size ? `(${item.file_size})` : ''}
                                            </div>
                                        </Link>
                                    );
                                })
                            ) : queryData.isLoading ? null : (
                                <div style={{ padding: '20px', textAlign: 'center' }}>
                                    {language?.noData || 'Không có dữ liệu'}
                                </div>
                            )}
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}

