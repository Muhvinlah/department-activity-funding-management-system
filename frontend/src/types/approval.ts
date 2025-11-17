// src/types/approval.ts
export interface TOR {
  id: number;
  activityName: string;
  purpose: string;
  background: string;
  participants: string;
  scheduleStart: string;
  scheduleEnd: string;
  budgetPlan: number;
  pic: string;
  status: TORStatus;
  submitterName: string;
  submitterNim: string;
  departmentName: string;
  submittedAt: string;
  currentStage: string;
  supportingDocuments: Document[];
  revisionHistory: Revision[];
  createdAt: string;
  updatedAt: string;
}

export interface LPJ {
  id: number;
  torId: number;
  activityName: string;
  activityResults: string;
  budgetRealization: number;
  documentation: string;
  evaluation: string;
  status: LPJStatus;
  submitterName: string;
  submittedAt: string;
  currentStage: string;
  supportingDocuments: Document[];
  revisionHistory: Revision[];
  torData: TOR; // Attached TOR data
  createdAt: string;
  updatedAt: string;
}

export interface Document {
  id: number;
  name: string;
  url: string;
  type: string;
  size: number;
  uploadedAt: string;
}

export interface Revision {
  id: number;
  notes: string;
  requestedBy: string;
  requestedAt: string;
  resolved: boolean;
}

export type TORStatus = 'draft' | 'submitted' | 'under_review' | 'needs_revision' | 'approved' | 'rejected';
export type LPJStatus = 'draft' | 'submitted' | 'under_review' | 'needs_revision' | 'approved' | 'rejected';