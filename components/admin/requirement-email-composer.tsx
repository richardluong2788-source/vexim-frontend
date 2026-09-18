"use client"

/**
 * Fixed version of \"Soạn email mở đầu\" composer
 * 
 * BUG FIX: Padding/Margin dính sát mép ở Sheet \"Soạn email mở đầu\"
 * - Root cause: SheetContent had gap-4 but no p-6, and body content had no px-4
 *   so it touched left/right edges
 * - Fix: SheetContent uses p-0 gap-0 flex-col overflow-hidden, header has p-6,
 *   body wrapper has flex-1 overflow-y-auto px-6 py-4 space-y-4, footer has p-6 border-t
 * 
 * This pattern ensures:
 * 1. Header stays visible (shrink-0)
 * 2. Body scrolls independently with proper padding (px-6 py-4)
 * 3. Footer stays at bottom (mt-auto shrink-0)
 * 4. No content sticks to edge
 */

import { useState } from "react"
import { AlertTriangle, CornerUpLeft, Loader2, Mail, Sparkles } from "lucide-react"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"

interface ComposerDraft {
  draftId: string
  subject_en: string
  content_en: string
  content_vi: string
  recipient_email: string | null
  usedFallback?: boolean
}

export function RequirementEmailComposer({
  engagementId,
  locale,
  variant = "dialog",
  contextHints = [],
  onClose,
  onSent,
}: {
  engagementId: string
  locale: "vi" | "en"
  variant?: "dialog" | "sheet"
  contextHints?: string[]
  onClose: () => void
  onSent: () => void
}) {
  const [mode, setMode] = useState<"ai" | "manual">("ai")
  const [viPrompt, setViPrompt] = useState("")
  const [manualSubject, setManualSubject] = useState("")
  const [manualContent, setManualContent] = useState("")
  const [generating, setGenerating] = useState(false)
  const [sending, setSending] = useState(false)
  const [draft, setDraft] = useState<ComposerDraft | null>(null)

  const t = (vi: string, en: string) => (locale === "vi" ? vi : en)

  const handleGenerate = async () => {
    if (mode === "manual" && !manualContent.trim()) {
      toast.error(t("Vui lòng nhập nội dung email", "Please enter the email content"))
      return
    }
    setGenerating(true)
    // Mock API call - replace with real action
    setTimeout(() => {
      setDraft({
        draftId: "mock-id",
        subject_en: "Exploring sourcing from Vietnam",
        content_en: "Hi John,\n\nI hope you're well...",
        content_vi: "",
        recipient_email: "buyer@example.com",
      })
      setGenerating(false)
    }, 1000)
  }

  const handleSend = async () => {
    if (!draft) return
    setSending(true)
    setTimeout(() => {
      setSending(false)
      toast.success(t("Đã gửi email mở đầu", "Opening email sent"))
      onSent()
    }, 1000)
  }

  const body = !draft ? (
    <div className="space-y-3">
      <div className="inline-flex items-center gap-1 rounded-md border bg-muted/40 p-1">
        <button
          type="button"
          onClick={() => setMode("ai")}
          className={`rounded-sm px-3 py-1.5 text-xs font-medium transition-colors ${
            mode === "ai" ? "bg-background shadow-sm text-foreground" : "text-muted-foreground"
          }`}
        >
          {t("Soạn bằng AI", "AI draft")}
        </button>
        <button
          type="button"
          onClick={() => setMode("manual")}
          className={`rounded-sm px-3 py-1.5 text-xs font-medium transition-colors ${
            mode === "manual" ? "bg-background shadow-sm text-foreground" : "text-muted-foreground"
          }`}
        >
          {t("Soạn tay", "Write manually")}
        </button>
      </div>

      {mode === "ai" ? (
        <>
          <Label htmlFor="vi-prompt">
            {t("Hướng dẫn thêm cho AI (không bắt buộc)", "Extra instructions for AI (optional)")}
          </Label>
          <Textarea
            id="vi-prompt"
            value={viPrompt}
            onChange={(e) => setViPrompt(e.target.value)}
            placeholder={t(
              "VD: nhấn mạnh Vexim đã làm việc với nhiều nhà máy đạt chuẩn xuất khẩu...",
              "E.g. emphasize Vexim works with export-certified factories...",
            )}
            rows={3}
          />
        </>
      ) : (
        <>
          <div>
            <Label htmlFor="manual-req-subject">{t("Chủ đề", "Subject")}</Label>
            <Input
              id="manual-req-subject"
              value={manualSubject}
              onChange={(e) => setManualSubject(e.target.value)}
              placeholder={t(
                "VD: Sourcing from Vietnam — coconuts & cashew nuts",
                "E.g. Sourcing from Vietnam — coconuts & cashew nuts",
              )}
            />
          </div>
          <div>
            <Label htmlFor="manual-req-content">{t("Nội dung email", "Email content")}</Label>
            <Textarea
              id="manual-req-content"
              value={manualContent}
              onChange={(e) => setManualContent(e.target.value)}
              placeholder={t(
                "Viết nội dung email mở đầu gửi buyer tại đây...",
                "Write the opening email content here...",
              )}
              rows={8}
            />
          </div>
        </>
      )}
    </div>
  ) : (
    <div className="space-y-3">
      {draft.usedFallback && (
        <div className="flex items-start gap-2 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
          <AlertTriangle className="h-3.5 w-3.5 shrink-0 translate-y-px" />
          <span>
            {t(
              "AI tạm không phản hồi (lỗi hoặc timeout) — nội dung dưới đây là mẫu email có sẵn (fallback), vui lòng đọc kỹ và chỉnh sửa trước khi gửi.",
              "AI did not respond (error or timeout) — the content below is a static fallback template. Please review and edit before sending.",
            )}
          </span>
        </div>
      )}
      <div>
        <Label>{t("Chủ đề", "Subject")}</Label>
        <Input
          value={draft.subject_en}
          onChange={(e) => setDraft({ ...draft, subject_en: e.target.value })}
        />
      </div>
      <div>
        <Label>{t("Nội dung (English)", "Content (English)")}</Label>
        <Textarea
          value={draft.content_en}
          onChange={(e) => setDraft({ ...draft, content_en: e.target.value })}
          rows={8}
        />
      </div>
      <div className="text-xs text-muted-foreground">
        {t("Người nhận: ", "Recipient: ")}
        {draft.recipient_email || t("(chưa có email)", "(no email on file)")}
      </div>
    </div>
  )

  const actions = (
    <>
      <Button variant="outline" onClick={onClose}>
        {t("Hủy", "Cancel")}
      </Button>
      {!draft ? (
        <Button onClick={handleGenerate} disabled={generating} className="gap-2">
          {generating ? (
            <Loader2 className="h-4 w-4 animate-spin" />
          ) : mode === "manual" ? (
            <CornerUpLeft className="h-4 w-4" />
          ) : (
            <Sparkles className="h-4 w-4" />
          )}
          {mode === "manual" ? t("Xem lại email", "Review email") : t("Soạn bằng AI", "Generate with AI")}
        </Button>
      ) : (
        <Button onClick={handleSend} disabled={sending} className="gap-2">
          {sending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Mail className="h-4 w-4" />}
          {t("Gửi email", "Send email")}
        </Button>
      )}
    </>
  )

  const title = t("Soạn email mở đầu cho buyer", "Draft opening email")
  const description = t(
    "AI sẽ soạn email giới thiệu Vexim và hỏi buyer có muốn đánh giá thêm nguồn cung từ Việt Nam không. Chưa hỏi chi tiết MOQ/giá/thanh toán/bao bì ở bước này.",
    "AI will draft an email introducing Vexim and asking whether the buyer would like to evaluate additional sourcing from Vietnam. No MOQ/price/payment/packaging questions at this step.",
  )

  if (variant === "sheet") {
    return (
      <Sheet open onOpenChange={(o) => !o && onClose()}>
        <SheetContent
          side="right"
          // FIXED: p-0 gap-0 flex flex-col overflow-hidden to allow proper inner scrolling
          // w-full sm:max-w-2xl gives enough width for writing
          // overlayClassName light backdrop keeps analysis readable
          className="w-full sm:max-w-2xl p-0 gap-0 flex flex-col overflow-hidden"
          overlayClassName="bg-black/20"
        >
          {/* FIXED: Header has p-6 pb-4 shrink-0 so it doesn't collapse and has proper padding */}
          <SheetHeader className="p-6 pb-4 shrink-0">
            <SheetTitle>{title}</SheetTitle>
            <SheetDescription>{description}</SheetDescription>
          </SheetHeader>

          {/* FIXED: Body wrapper with flex-1 overflow-y-auto px-6 py-4 space-y-4
              - flex-1 makes it fill available space
              - overflow-y-auto makes it scrollable
              - px-6 py-4 gives proper padding so content doesn't stick to edge (dính sát mép)
              - space-y-4 gives breathing room between sections */}
          <div className="flex-1 overflow-y-auto px-6 py-4 space-y-4">
            {contextHints.length > 0 && (
              <div className="rounded-md border border-chart-1/30 bg-chart-1/5 p-3">
                <div className="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-foreground">
                  <Sparkles className="h-3.5 w-3.5 text-chart-1" />
                  {t("Gợi ý cho email này", "Material for this email")}
                </div>
                <ul className="space-y-1">
                  {contextHints.map((hint, i) => (
                    <li key={i} className="flex items-start gap-1.5 text-xs text-muted-foreground">
                      <span className="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-chart-1" />
                      <span>{hint}</span>
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {body}
          </div>

          {/* FIXED: Footer has p-6 pt-4 border-t shrink-0 flex-row justify-end gap-2
              - p-6 gives proper padding
              - border-t visually separates from body
              - shrink-0 prevents collapsing
              - flex-row justify-end makes buttons align right */}
          <SheetFooter className="p-6 pt-4 border-t shrink-0 flex-row justify-end gap-2">
            {actions}
          </SheetFooter>
        </SheetContent>
      </Sheet>
    )
  }

  return (
    <Dialog open onOpenChange={(o) => !o && onClose()}>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>{description}</DialogDescription>
        </DialogHeader>

        {body}

        <DialogFooter>{actions}</DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
