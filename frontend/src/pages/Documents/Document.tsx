import appStyles from '~styles/App.module.css';
import styles from './styles/Document.module.css';

import { useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect, useState } from 'react';
import { MdDeleteOutline, MdEdit } from 'react-icons/md';
import { Navigate, useNavigate, useParams } from 'react-router';
import { apiDeleteDocument, apiGetDocumentById, getDocumentViewUrl } from '~api/document';
import Loading from '~components/Loading';
import YesNoPopUp from '~components/YesNoPopUp';
import QUERY_KEYS from '~constants/query-keys';
import useAppContext from '~hooks/useAppContext';
import useLanguage from '~hooks/useLanguage';
import NotFound from '~pages/Errors/NotFound';
import css from '~utils/css';
import CreateDocument from './components/CreateDocument';

export default function Document() {
    const { id } = useParams();
    const { permissions, appTitle, user } = useAppContext();
    const language = useLanguage('page.document');
    const queryClient = useQueryClient();
    const [showDeletePopUp, setShowDeletePopUp] = useState(false);
    const [showEditPopUp, setShowEditPopUp] = useState(false);
    const navigate = useNavigate();
    const queryData = useQuery({
        queryKey: [QUERY_KEYS.DOCUMENT_DETAIL, { id: id }],
        queryFn: () => apiGetDocumentById(String(id)),
        enabled: permissions.has('document_view'),
        retry: false,
        refetchOnWindowFocus: false,
    });
    const handleDeleteDocument = async () => {
        await apiDeleteDocument(String(id));
    };
    const onMutateSuccess = () => {
        [QUERY_KEYS.PAGE_DOCUMENTS].forEach(key => {
            queryClient.refetchQueries({ queryKey: [key] });
        });
        navigate('/documents');
    };
    useEffect(() => {
        return () => {
            queryClient.removeQueries({ queryKey: [QUERY_KEYS.DOCUMENT_DETAIL, { id: id }] });
        };
    }, [id, queryClient]);
    useEffect(() => {
        if (!queryData.data) return;
        appTitle.setAppTitle(queryData.data.title);
    }, [appTitle, queryData.data]);

    if (!permissions.has('document_view')) return <Navigate to='/' />;
    if (queryData.error) return (
        <main className={css(appStyles.dashboard, styles.pageContent)}>
            <NotFound />
        </main>
    );
    return (
        <>
            {showEditPopUp && queryData.data ?
                <CreateDocument
                    data={queryData.data}
                    onMutateSuccess={() => { 
                        queryData.refetch();
                        setShowEditPopUp(false);
                    }}
                    setShowPopUp={setShowEditPopUp}
                />
                : null
            }
            {showDeletePopUp === true ?
                <YesNoPopUp
                    message={language?.deleteMessage || 'Bạn có chắc chắn muốn xóa tài liệu này?'}
                    mutateFunction={handleDeleteDocument}
                    setShowPopUp={setShowDeletePopUp}
                    onMutateSuccess={onMutateSuccess}
                    langYes={language?.langYes || 'Có'}
                    langNo={language?.langNo || 'Không'}
                /> : null}
            <main className={css(appStyles.dashboard, styles.pageContent)}>
                {
                    queryData.isLoading ? <Loading /> : null
                }
                {
                    queryData.data ?
                        <>
                            <section className={styles.formContent}>
                                <div className={styles.header}>
                                    <h2 className={styles.title}>{queryData.data.title}</h2>
                                    {permissions.hasAnyFormList(['document_update', 'document_delete']) && (
                                        <div className={styles.actionButtons}>
                                            {permissions.has('document_update') && (
                                                <button
                                                    type='button'
                                                    onClick={() => setShowEditPopUp(true)}
                                                    className={appStyles.actionItemWhite}
                                                >
                                                    <MdEdit /> {language?.edit || 'Chỉnh sửa'}
                                                </button>
                                            )}
                                            {permissions.has('document_delete') && (
                                                <button
                                                    type='button'
                                                    onClick={() => setShowDeletePopUp(true)}
                                                    className={appStyles.actionItemWhiteBorderRed}
                                                >
                                                    <MdDeleteOutline /> {language?.delete || 'Xóa'}
                                                </button>
                                            )}
                                        </div>
                                    )}
                                </div>
                                {queryData.data.description && (
                                    <div className={styles.description}>
                                        <p>{queryData.data.description}</p>
                                    </div>
                                )}
                                <div className={styles.documentInfo}>
                                    <div className={styles.infoItem}>
                                        <strong>Tên file:</strong> {queryData.data.file_name}
                                    </div>
                                    {queryData.data.file_size && (
                                        <div className={styles.infoItem}>
                                            <strong>Kích thước:</strong> {queryData.data.file_size}
                                        </div>
                                    )}
                                </div>
                                <div className={styles.viewerActions}>
                                    <a
                                        href={getDocumentViewUrl(queryData.data.id, true)}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className={css(appStyles.actionItem, styles.viewButton)}
                                    >
                                        Xem PDF
                                    </a>
                                </div>
                            </section>
                            <section className={styles.pdfViewer}>
                                <iframe
                                    src={getDocumentViewUrl(queryData.data.id)}
                                    className={styles.iframe}
                                    title={queryData.data.title}
                                />
                            </section>
                        </> : null
                }
            </main>
        </>
    );
}

